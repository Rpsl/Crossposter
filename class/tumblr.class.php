<?php
/*
    Rpsl 2009

    Класс для отправки фотографий на сервис tumblr

    Установка:

    Выполните
       ALTER TABLE `crossposter` ADD `tumblr` ENUM( '0', '1' ) NOT NULL DEFAULT '0'
    в базе данных.

    Настройки для config.inc.php

    $config['SERVERS'][] = 'tumblr';
    $config['tumblr']['login']          = '';
    $config['tumblr']['password']       = '';
    $config['tumblr']['private']        = ''; // 1 or 0

*/

class tumblr {

    function send($item, $config) {

        $sql = "SELECT  `tumblr`
                FROM    `posts`
                WHERE   `link` = '".mysql_real_escape_string($item['link'])."'";

        $status = mysql_fetch_assoc(mysql_query($sql));

        if(!isset($status['tumblr'])){
            $count = 0;
        }else{
            $count = 1;
        }

        if($count == 0 OR $status['tumblr'] == 0){

            preg_match_all('/(img src=")(.*)(")([\s]+)(border)/Usmi', $item['description'], $matches);

            $tmp['image']       = $matches[2][0];
            $tmp['link_photo']  = str_replace('s320', 's1600', $tmp['image']);

            if(!isset($item['title'])){
                $item['title'] = 'mobile';
            }

            $post['email']      = $config['login'];
            $post['password']   = $config['password'];
            $post['generator']  = 'Rpsl crossposter';
            $post['privat']     = $config['private'];
            $post['type']       = 'photo';
            $post['source']     = $tmp['link_photo'];
            $post['caption']    = $item['title'];

            unset($tmp);

            $curl = curl_init();

                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_URL, 'http://www.tumblr.com/api/write');
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);

            curl_exec($curl);

            $result = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            if($result == 201){
                if($count == 0){
                    mysql_query("
                                    INSERT INTO `posts`
                                        (`link`, `tumblr`)
                                    VALUES
                                        ('".mysql_real_escape_string($item['link'])."', '1');
                                ");
                }else{
                    mysql_query("
                                    UPDATE `posts`
                                    SET `tumblr` = '1'
                                    WHERE `link` = '".mysql_real_escape_string($item['link'])."'
                                ");
                }
            }
        }
    }
}
