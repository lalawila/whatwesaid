<?php

    error_reporting(E_ALL);
    ini_set('display_errors', true);
    ini_set('html_errors', false);

    define( 'WWSINC', 'includes/' );
    define( 'WWSPAGE', 'pages/' );
    define( 'WWSTP', 'pages/template-parts/' );
    
    

    // we've writen this code where we need
    //function __autoload($classname) {
    //    $filename = ABSPATH . WWSINC . 'class-' . $classname . '.php';
    //    include_once ($filename);
    //}

    require_once ( ABSPATH . 'conf.php'); 
    require_once ( ABSPATH . WWSINC . 'constants.php' );
    require_once ( ABSPATH . WWSINC . 'functions.php' );
    require_once ( ABSPATH . WWSINC . 'class-ws-user.php' );
    require_once ( ABSPATH . WWSINC . 'formatting.php' );
    require_once ( ABSPATH . WWSINC . 'class-ws-db.php' );
    require_once ( ABSPATH . WWSINC . 'class-ws-article.php' );
    require_once ( ABSPATH . WWSINC . 'class-wws.php' );
    require_once ( ABSPATH . WWSINC . 'class-ws-error.php' ); 
    require_once ( ABSPATH . WWSINC . 'class-ws-comment.php' ); 
    require_once ( ABSPATH . WWSINC . 'l10n.php' );
    require_once ( ABSPATH . WWSINC . 'cache.php' );
    require_once ( ABSPATH . WWSINC . 'class-smtp.php' );
    require_once ( ABSPATH . WWSINC . 'class-phpmailer.php' );
    require_once ( ABSPATH . WWSINC . 'class-ws-pluggable.php' );
    
    init_lang();
    ws_cache_init();
    

    $GLOBALS['splited_uri'] = split_uri();
    $GLOBALS['wsdb'] = new WS_DB(sql_username, sql_password, sql_database, sql_hostname);
    $GLOBALS['article'] = new WS_Article;
    $GLOBALS['user'] = WS_UserManage::instance();
    $GLOBALS['wws'] = new WWS;

    date_default_timezone_set('UTC'); 
    $GLOBALS['user']->check_login();
    

    
