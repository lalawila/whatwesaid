<?php

    error_reporting(E_ALL);
    ini_set('display_errors', true);
    ini_set('html_errors', false);

    define( 'WWSINC', 'includes/' );
    define( 'WWSPAGE', 'pages/' );
    define( 'WWSTP', 'pages/template-parts/' );
    
    
    define( 'SITE_EN', 'https://whatwesaid.xyz');
    define( 'SITE_ZH', 'https://zh.whatwesaid.xyz');
    

    // we've writen this code where we need
    //function __autoload($classname) {
    //    $filename = ABSPATH . WWSINC . 'class-' . $classname . '.php';
    //    include_once ($filename);
    //}
    
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
    
    $stf = fopen("setting-data.json", "r") or die("cant find setting-data.son");
    $data = file_get_contents("setting-data.json");
    $jsdata = json_decode ($data, true);
    fclose($stf);

    $GLOBALS['page'] = get_page_name();
    $GLOBALS['wsdb'] = new WS_DB($jsdata['MySql']['username'],$jsdata['MySql']['password'],$jsdata['MySql']['database'], $jsdata['MySql']['hostname']);
    $GLOBALS['article'] = new WS_Article;
    $GLOBALS['wws'] = new WWS;

    date_default_timezone_set('UTC'); 
    WS_User::check_login();
    

    
