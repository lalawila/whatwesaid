<?php
final class WS_Comment{
    private$_sql = null;
    public function __construct( $db = Null ){
        global $splited_uri;

        if (isset($db) && $db instanceof WS_DB)
            $this->_sql = $db;
        else
            $this->_sql = $GLOBALS['wsdb'];

    }


    public static function get_comments( $article_id = '', $page = 1, $per_page = 10, $nav = true){
        if($nav)
            WS_Comment::show_nav();
    }
    public static function get_comment($key = ['comment_id'=>'']){
        
    }
    
    public function add_comment( $content, $user = Null ){
        if( is_null($user))
            $user = WS_User::get_current_user();
        if(mb_strlen($content) > 140){
            WS_Error::add_error(__('comment over 140'));
            return false;
        }
    }


    public static function show_nav(){
        
    }

}
