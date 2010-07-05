<?php

    mysql_connect( 'localhost', 'login', 'password' ) or die( 'db' );
    mysql_select_db( 'crossposter' );
    mysql_query( "SET CHARACTER SET UTF-8" );


    function autoload ( $ClassName )
    {
        $fileName = $ClassName . '.class.php';
        include ( 'class/' . $fileName );
    }

    spl_autoload_register( 'autoload' );
