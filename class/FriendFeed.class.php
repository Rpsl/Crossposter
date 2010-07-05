<?php

/*
    Rpsl 2009

    Класс для отправки фотографий на сервис FrienFeed

    Установка:

    Выполните
       ALTER TABLE `crossposter` ADD `friendfeed` ENUM( '0', '1' ) NOT NULL DEFAULT '0'
    в базе данных.

    Настройки для config.inc.php

    $config['SERVERS'][] = 'FriendFeed';
    $config['FriendFeed']['login']        = '';
    $config['FriendFeed']['remotekey']     = '';

*/

class FriendFeed
{


    public function send ( $item, $config )
    {

        $sql = "SELECT  `friendfeed`
                FROM    `posts`
                WHERE   `link` = '" . mysql_real_escape_string( $item [ 'link' ] ) . "'";

        $status = mysql_fetch_assoc( mysql_query( $sql ) );

        if ( !isset( $status [ 'friendfeed' ] ) )
        {
            $count = 0;
        }
        else
        {
            $count = 1;
        }

        if ( $count == 0 or $status [ 'friendfeed' ] == 0 )
        {

            preg_match_all( '/(img src=")(.*)(")([\s]+)(border)/Usmi', $item [ 'description' ], $matches );

            $tmp [ 'image' ] = $matches [ 2 ] [ 0 ];
            $tmp [ 'link_photo' ] = str_replace( 's320', 's1600', $tmp [ 'image' ] );

            if ( !isset( $item [ 'title' ] ) )
            {
                $item [ 'title' ] = 'mobile';
            }

            $post [ 'body' ] = $item [ 'title' ];
            $post [ 'image_url' ] = $tmp [ 'link_photo' ];

            unset( $tmp );

            $curl = curl_init();

            curl_setopt( $curl, CURLOPT_CONNECTTIMEOUT, 2 );
            curl_setopt( $curl, CURLOPT_HEADER, false );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $curl, CURLOPT_BINARYTRANSFER, 0 );
            curl_setopt( $curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
            curl_setopt( $curl, CURLOPT_USERPWD, $config [ 'login' ] . ":" . $config [ 'remotekey' ] );
            curl_setopt( $curl, CURLOPT_URL, 'http://friendfeed-api.com/v2/entry' );
            curl_setopt( $curl, CURLOPT_POST, 3 );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, $post );

            $result = curl_exec( $curl );
            curl_close( $curl );

            if ( !strpos( $result, 'errorCode' ) )
            {
                if ( $count == 0 )
                {
                    mysql_query( "
                                    INSERT INTO `posts`
                                        (`link`, `friendfeed`)
                                    VALUES
                                        ('" . mysql_real_escape_string( $item [ 'link' ] ) . "', '1');
                                " );
                }
                else
                {
                    mysql_query( "
                                    UPDATE `posts`
                                    SET `friendfeed` = '1'
                                    WHERE `link` = '" . mysql_real_escape_string( $item [ 'link' ] ) . "'
                                " );
                }
            }
        }
    }
}

?>
