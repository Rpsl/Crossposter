<?php

/*
    Rpsl 2009

    Класс для отправки фотографий в Facebook.
    Вам обязательно нужно создать своё приложение на facebook, просто получить api ключи.

    Фотографии будут попадать в альбом вашего приложения, после чего уже вручную их нужно одобрить.

    Установка:

    Выполните
       ALTER TABLE `crossposter` ADD `facebook` ENUM( '0', '1' ) NOT NULL DEFAULT '0'
    в базе данных.

    Настройки для config.inc.php

    $config['SERVERS'][] = 'FaceBook';
    $config['FaceBook']['apikey']         = '';
    $config['FaceBook']['apisec']         = '';
    $config['FaceBook']['uid']            = '';  // Ваш user_id

*/

class FaceBook
{


    public function send ( $item, $config )
    {

        $sql = "SELECT  `facebook`
                FROM    `posts`
                WHERE   `link` = '" . mysql_real_escape_string( $item [ 'link' ] ) . "'";

        $status = mysql_fetch_assoc( mysql_query( $sql ) );

        if ( !isset( $status [ 'facebook' ] ) )
        {
            $count = 0;
        }
        else
        {
            $count = 1;
        }

        if ( $count == 0 or $status [ 'facebook' ] == 0 )
        {

            preg_match_all( '/(img src=")(.*)(")([\s]+)(border)/Usmi', $item [ 'description' ], $matches );

            $tmp [ 'image' ] = $matches [ 2 ] [ 0 ];
            $tmp [ 'link_photo' ] = str_replace( 's320', 's1600', $tmp [ 'image' ] );

            $filename = tempnam( ini_get( 'upload_tmp_dir' ), 'cron' );

            $fh = fopen( $filename, 'a' );
            fwrite( $fh, file_get_contents( $tmp [ 'link_photo' ], FILE_BINARY ) );

            $args = array (
                'method' => 'photos.upload', 'v' => '1.0', 'api_key' => $config [ 'apikey' ], 'uid' => $config [ 'uid' ], 'call_id' => microtime( true ), 'format' => 'XML', 'caption' => $item [ 'title' ]
            );

            FaceBook::signRequest( $args, $config [ 'apisec' ] );

            $args [ basename( $filename ) ] = '@' . $filename;

            $ch = curl_init();
            $url = 'http://api.facebook.com/restserver.php?method=photos.upload';
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $args );
            $result = curl_exec( $ch );

            fclose( $fh );

            if ( strpos( $result, 'created' ) )
            {
                if ( $count == 0 )
                {
                    mysql_query( "
                                    INSERT INTO `posts`
                                        (`link`, `facebook`)
                                    VALUES
                                        ('" . mysql_real_escape_string( $item [ 'link' ] ) . "', '1');
                                " );
                }
                else
                {
                    mysql_query( "
                                    UPDATE `posts`
                                    SET `facebook` = '1'
                                    WHERE `link` = '" . mysql_real_escape_string( $item [ 'link' ] ) . "'
                                " );
                }
            }
        }
    }


    private function signRequest ( &$args, $secret )
    {

        ksort( $args );
        $sig = '';

        foreach ( $args as $k => $v )
        {
            $sig .= $k . '=' . $v;
        }

        $sig .= $secret;
        $args [ 'sig' ] = md5( $sig );
    }

}

?>
