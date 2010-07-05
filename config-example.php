<?php
    $rss_url = "http:/name.blogspot.com/feeds/posts/default?alt=rss";

    $config['SERVERS'][] = 'LiveJournal';
    $config['LiveJournal']['login']    = '';
    $config['LiveJournal']['password'] = ''; // md5 hash
    $config['LiveJournal']['prefix']   = 'Mobile bloging: ';

    $config['SERVERS'][] = 'TwitPic';
    $config['TwitPic']['login']        = '';
    $config['TwitPic']['password']     = '';

    #$config['SERVERS'][] = 'vkontakte';
    $config['vkontakte']['id']         = ;
    $config['vkontakte']['login']      = '';
    $config['vkontakte']['pass']       = '';
    $config['vkontakte']['album_id']   = ;
    $config['vkontakte']['proxy_enable'] = 1;
    $config['vkontakte']['proxy']      = '';
    $config['vkontakte']['proxy_auth'] = '';

    #$config['SERVERS'][] = 'YandexFotki';
    $config['YandexFotki']['album_id'] = '';
    $config['YandexFotki']['login']    = '';
    $config['YandexFotki']['password'] = '';

    #$config['SERVERS'][] = 'FriendFeed';
    $config['FriendFeed']['login']      = '';
    $config['FriendFeed']['remotekey']  = '';

    #$config['SERVERS'][] = 'FaceBook';
    $config['FaceBook']['apikey']       = '';
    $config['FaceBook']['apisec']       = '';
    $config['FaceBook']['uid']          = '';

    #$config['SERVERS'][] = 'tumblr';
    $config['tumblr']['login']          = '';
    $config['tumblr']['password']       = '';
    $config['tumblr']['private']        = '1';
