<?php
class WS_Error {

	private static $errors = array();

	public static function get_errors() {
		return WS_Error::$errors;
	}
    
    public static function add_error ($error) {
        
        array_unshift(WS_Error::$errors, $error);
    }

	public static function get_error() {
		return isset(WS_Error::$errors[0])?WS_Error::$errors[0]:'';
	}
    
    public static function reset() {
        WS_Error::$errors = array();
    }
    public static function print_error($before = '', $after = '') {
        foreach (WS_Error::$errors as $error){
            printf('%s' . $error . '%s', $before, $after);
        }
    }
    public static function has_error(){
        if(empty($errors)){
            return false;
        }
        return true;
    }
}
