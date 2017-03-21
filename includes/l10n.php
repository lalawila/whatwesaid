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
    
    $domain  =  'wws' ;                     //��������������ȡ������������֣�����Ҫ����Ӧ��.mo�ļ����ļ�����ͬ����������չ������
    bindtextdomain ( $domain ,  "locale/" ); //����ĳ�����mo�ļ�·��    
    bind_textdomain_codeset($domain ,  'UTF-8' );  //����mo�ļ��ı���ΪUTF-8    
    textdomain($domain );                    //����gettext()�������ĸ���ȥ��mo�ļ�    
    return $lang;
}

function get_lang (){
    return isset($GLOBALS['lang']) ? $GLOBALS['lang'] : init_lang();
}