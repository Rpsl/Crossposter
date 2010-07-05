<?php

/*
    Rpsl 2009

    Класс для отправки фотографий на сервис twitpic +
    автоматическая публикация фотографий в twitter.

    Установка:

    Выполните
       ALTER TABLE `crossposter` ADD `twitpic` ENUM( '0', '1' ) NOT NULL DEFAULT '0'
    в базе данных.

    Настройки для config.inc.php

    $config['SERVERS'][] = 'TwitPic';
    $config['TwitPic']['login']        = '';
    $config['TwitPic']['password']     = '';

*/

class TwitPic
{
    function send ( $item, $config )
    {

        $sql = "SELECT  `twitpic`
                FROM    `posts`
                WHERE   `link` = '" . mysql_real_escape_string( $item [ 'link' ] ) . "'";

        $status = mysql_fetch_assoc( mysql_query( $sql ) );

        if ( !isset( $status [ 'twitpic' ] ) )
        {
            $count = 0;
        }
        else
        {
            $count = 1;
        }

        if ( $count == 0 or $status [ 'twitpic' ] == 0 )
        {

            preg_match_all( '/(img src=")(.*)(")([\s]+)(border)/Usmi', $item [ 'description' ], $matches );

            $tmp [ 'image' ] = $matches [ 2 ] [ 0 ];
            $tmp [ 'link_photo' ] = str_replace( 's320', 's1600', $tmp [ 'image' ] );

            $filename = tempnam( ini_get( 'upload_tmp_dir' ), 'cron' );

            $fh = fopen( $filename, 'a' );
            fwrite( $fh, file_get_contents( $tmp [ 'link_photo' ], FILE_BINARY ) );

            $post [ 'username' ] = $config [ 'login' ];
            $post [ 'password' ] = $config [ 'password' ];
            $post [ 'message' ] = $item [ 'title' ];
            $post [ 'media' ] = '@' . $filename;

            unset( $tmp );

            $curl = curl_init();

            curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 2 );
            curl_setopt( $curl, CURLOPT_HEADER, false );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_BINARYTRANSFER, 0 );
            curl_setopt( $curl, CURLOPT_URL, 'http://twitpic.com/api/uploadAndPost' );
            #curl_setopt($curl, CURLOPT_URL, 'http://twitpic.com/api/upload'); // <rsp stat="ok">
            curl_setopt( $curl, CURLOPT_POST, 3 );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $post );

            $result = curl_exec( $curl );

            curl_close( $curl );
            fclose( $fh );

            if ( strpos( $result, '<rsp status="ok">' ) )
            {
                if ( $count == 0 )
                {
                    mysql_query( "
                                    INSERT INTO `posts`
                                        (`link`, `twitpic`)
                                    VALUES
                                        ('" . mysql_real_escape_string( $item [ 'link' ] ) . "', '1')
                                " );
                }
                else
                {
                    mysql_query( "
                                    UPDATE  `posts`
                                    SET     `twitpic` = '1'
                                    WHERE   `link` = '" . mysql_real_escape_string( $item [ 'link' ] ) . "'
                                    LIMIT 1;
                                " );
                }
            }
        }
    }
}

?>
