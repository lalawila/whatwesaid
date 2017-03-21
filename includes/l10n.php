<?php
function _e( $str ){
    echo gettext($str);
}
function __( $str ){
    return gettext($str);
}
function init_lang(){
    $lang = explode('.', $_SERVER['HTTP_HOST']);
    $lang = $lang[0];
    $tt='zh_CN.UTF-8';
    if($lang !== 'zh'){
        $lang = 'en';
        $tt='en_US.UTF-8';
    }
    $GLOBALS['lang'] = $lang;
    putenv("LANGUAGE=$tt" );
    putenv("LANG=$tt" );   
    setlocale(LC_ALL ,$tt);
    
    $domain  =  'wws' ;                     //域名，可以任意取个有意义的名字，不过要跟相应的.mo文件的文件名相同（不包括扩展名）。
    bindtextdomain ( $domain ,  "locale/" ); //设置某个域的mo文件路径    
    bind_textdomain_codeset($domain ,  'UTF-8' );  //设置mo文件的编码为UTF-8    
    textdomain($domain );                    //设置gettext()函数从哪个域去找mo文件    
    return $lang;
}

function get_lang (){
    return isset($GLOBALS['lang']) ? $GLOBALS['lang'] : init_lang();
}