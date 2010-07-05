<?php

    error_reporting( E_ALL );
    ini_set( 'display_errors', 1 );

    $FOLDER = '/crossposter';

    include_once ( $_SERVER [ 'DOCUMENT_ROOT' ] . $FOLDER . '/lib/rss_fetch.inc' );
    include_once ( $_SERVER [ 'DOCUMENT_ROOT' ] . $FOLDER . '/functions.php' );
    include_once ( $_SERVER [ 'DOCUMENT_ROOT' ] . $FOLDER . '/config.php' );

    $rss = fetch_rss( $rss_url );

    /*
      Основной элемент.
      В цикле создаются объекты доступных классов и вызывается их функций send()
    */

    $pusk = "$\$server = new \$server; $\$server->send(\$item, \$config[\$server]);";

    foreach ( $rss->items as $item )
    {
        foreach ( $config [ 'SERVERS' ] as $server )
        {
            eval( $pusk );
        }
    }
