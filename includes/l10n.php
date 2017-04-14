<?php
function _e( $str ){
    echo $str;
}
function __( $str ){
    return $str;
}
function init_lang(){
    $lang = explode('.', $_SERVER['HTTP_HOST']);
    $lang = $lang[0];
    if($lang !== 'zh'){
        $lang = 'en';
    }
    $GLOBALS['lang'] = $lang;
    return $lang;
}

function get_lang (){
    return isset($GLOBALS['lang']) ? $GLOBALS['lang'] : init_lang();
}
