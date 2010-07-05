<?php

/*

    Rpsl 2009

    Класс для публикация фотографий в livejournal.
    Все посты создаются в закрытом режиме, с маской "Для друзей",
    Для отключения данной опции - закомментируйте строки:

                "security"          => new xmlrpcval("usemask", "string"),
                "allowmask"         => new xmlrpcval("1", "string"),


    Установка:

    Выполните
       ALTER TABLE `crossposter` ADD `twitpic` ENUM( '0', '1' ) NOT NULL DEFAULT '0'
    в базе данных.

    Настройки для config.inc.php

    $config['SERVERS'][] = 'LiveJournal';
    $config['LiveJournal']['login']    = '';
    $config['LiveJournal']['password'] = ''; // md5 вашего пароля ( http://www.google.ru/search?hl=ru&source=hp&q=online+md5&btnG=Поиск+в+Google&lr=&aq=f&oq= )
    $config['LiveJournal']['prefix']   = 'Mobile bloging: '; // Префикс для названия сообщения.

*/


class LiveJournal {

    /*
     * function send
     * @param  $item    - array;
     * @param  $status  - int (new (0) or update (1))
     */

    public function send( $item , $config )
    {

        $sql = "SELECT  `livejournal`
                FROM    `posts`
                WHERE   `link` = '".mysql_real_escape_string( $item['link'] )."'";

        $status = mysql_fetch_assoc( mysql_query( $sql ) );

        if( !isset( $status['livejournal'] ) )
        {
            $count = 0;
        }
        else
        {
            $count = 1;
        }

        if( $count == 0 OR $status['livejournal'] == 0 ){

            include_once("lib/xmlrpc.inc");

            $subj   = $config['prefix'].$item['title'];
            $url    = $item['link'];

            $text   = "<center>".htmlspecialchars_decode($item['description'])."</center>";

            /*
             Если вдруг захотим постить фотки задним числом.
             При включенни не будут попадать во френделенту.
            */

            /*
            $time    = explode("T", $item['atom']['updated']); // 2008-07-10T14:32:03.579+04:00
            $time_d  = explode("-", $time[0]);
            $time_t  = explode(":", $time[1]);

            $year = $time_d[0];
            $mon  = $time_d[1];
            $day  = $time_d[2];
            $hour = $time_t[0];
            $min  = $time_t[1];
            */

            /*
             Оставленно на случай включения постинга "задним" числом
            */

            $year = date('Y');
            $mon  = date('m');
            $day  = date('d');
            $hour = date('H');
            $min  = date('i');

            $post = array(
                "username"          => new xmlrpcval($config['login'], "string"),
                "hpassword"         => new xmlrpcval($config['password'], "string"),
                "event"             => new xmlrpcval($text, "string"),
                "subject"           => new xmlrpcval($subj, "string"),
                "security"          => new xmlrpcval("usemask", "string"),
                "allowmask"         => new xmlrpcval("1", "string"),
                "lineendings"       => new xmlrpcval("unix", "string"),


                "props"             => new xmlrpcval(array(
                                                     //'opt_backdated' => new xmlrpcval('1', "string")),
                                                     'opt_preformatted' => new xmlrpcval(true, "string")),
                                                     "struct"
                                                     ),


                "year"              => new xmlrpcval($year, "int"),
                "mon"               => new xmlrpcval($mon, "int"),
                "day"               => new xmlrpcval($day, "int"),
                "hour"              => new xmlrpcval($hour, "int"),
                "min"               => new xmlrpcval($min, "int"),
                "ver"               => new xmlrpcval(1, "int")
            );

            $post2 = array(
                new xmlrpcval($post, "struct")
            );

            $f = new xmlrpcmsg('LJ.XMLRPC.postevent', $post2);
            $c = new xmlrpc_client("/interface/xmlrpc", "www.livejournal.com", 80);
            $r = $c->send($f);

            if(!$r->faultCode()){
                if($count == 0){
                    mysql_query("
                                    INSERT INTO `posts`
                                        (`link`, `livejournal`)
                                    VALUES
                                        ('".mysql_real_escape_string($item['link'])."', '1')
                                ");
                }else{
                    mysql_query("
                                    UPDATE `posts`
                                    SET `livejournal` = '1'
                                    WHERE `link` = '".mysql_real_escape_string($item['link'])."'
                                    LIMIT 1;
                                ");
                }
            }
        }
    }
}

?>
