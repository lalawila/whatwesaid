<?php

function get_header( ) {

    require_once (ABSPATH . WWSTP . 'header.php');
}
function get_site_url() {
    if (get_locale() == 'en'){
        return SITE_EN;
    }
    elseif (get_locale() == 'zh') {
        return SITE_ZH;
    }
    
    return false;
}
function load_style( $style = 'style.css' ) {
    printf('<link href="%s" type="text/css" rel="stylesheet">',
        '/content/css/' . $style);
    //load_ckeditor();
}
function load_script( $script ) {
	printf('<script src="%s" type="text/javascript"></script>', '/content/js/' . $script);
}
function page_404() {
    require_once (ABSPATH . 'pages/404.php');
}

function get_sidebar() {
    include_once (ABSPATH . WWSTP . 'sidebar.php');
}
function get_footer() {
    include_once (ABSPATH . WWSTP . 'footer.php');
}
function split_uri() {

	//if ($_SERVER['REQUEST_URI'] == '/')
	//	return 'home';
	//$page = null;
	//if (preg_match ('@.*\/(.*?)(?:\?|$)@' ,$_SERVER['REQUEST_URI'],$page))
       	//	return $page[1];
        //return '404';

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
