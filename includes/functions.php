<?php

function get_header( $func = null ) {
    require_once (ABSPATH . WWSTP . 'header.php');
}

function get_site_url() {
    if (get_locale() == 'en'){
        return en_url;
    }
    elseif (get_locale() == 'zh') {
        return zh_url;
    }
    
    return false;
}

function load_style( $style = 'style.css' ) {
    $filename = '/content/css/' . $style;
    $filename = get_version($filename);
    printf('<link href="%s" type="text/css" rel="stylesheet">',$filename);
}

function load_script( $script ) {
    $filename = '/content/js/' . $script;
    $filename = get_version($filename);
	printf('<script src="%s" type="text/javascript"></script>', $filename);
}

function get_version( $filename ){
    global $redis;
    if( !($fnv = $redis->hGet('LocVersion', $filename)) ) {
        $version = md5_file( ABSPATH . $filename );
        $fnv = $filename . '?v=' . $version;    
        $redis->hSet('LocVersion', $filename, $fnv);
    }
    return $fnv;
}

function load_ckeditor() {
    echo '<script src="/content/ckeditor/ckeditor.js"></script>';
}

function page_404() {
    require_once (ABSPATH . 'pages/404.php');
    exit();
}


function get_footer() {
    include_once (ABSPATH . WWSTP . 'footer.php');
}
function split_uri() {

    $uri = null;
    preg_match_all( '@\/([^\/\?]+)@', $_SERVER['REQUEST_URI'], $uri );
    return empty($uri[1]) ? ['home']: $uri[1];
}
function get_locale() {
    //不精确，得修改
    return $GLOBALS['lang'];
}
/**
 * Temporarily suspend cache additions.
 *
 * Stops more data being added to the cache, but still allows cache retrieval.
 * This is useful for actions, such as imports, when a lot of data would otherwise
 * be almost uselessly added to the cache.
 *
 * Suspension lasts for a single page load at most. Remember to call this
 * function again if you wish to re-enable cache adds earlier.
 * @staticvar bool $_suspend
 *
 * @param bool $suspend Optional. Suspends additions if true, re-enables them if false.
 * @return bool The current suspend setting
 */
function ws_suspend_cache_addition( $suspend = null ) {
	static $_suspend = false;

	if ( is_bool( $suspend ) )
		$_suspend = $suspend;

	return $_suspend;
}

    
function mbstring_binary_safe_encoding( $reset = false ) {
	static $encodings = array();
	static $overloaded = null;

	if ( is_null( $overloaded ) )
		$overloaded = function_exists( 'mb_internal_encoding' ) && ( ini_get( 'mbstring.func_overload' ) & 2 );

	if ( false === $overloaded )
		return;

	if ( ! $reset ) {
		$encoding = mb_internal_encoding();
		array_push( $encodings, $encoding );
		mb_internal_encoding( 'ISO-8859-1' );
	}

	if ( $reset && $encodings ) {
		$encoding = array_pop( $encodings );
		mb_internal_encoding( $encoding );
	}
}
function reset_mbstring_encoding() {
	mbstring_binary_safe_encoding( true );
}
