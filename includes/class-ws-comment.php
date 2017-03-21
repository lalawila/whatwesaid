<?php
class WS_Comment{
    public static function load_ckeditor(){
        echo '<script src="//cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script>';
    }
    public static function get_comments( $article_id = '', $page = 1, $per_page = 10, $nav = true){
        
        
        if($nav)
            WS_Comment::show_nav();
    }
    public static function get_comment($key = ['comment_id'=>'']){
        
    }
    
    public static function show_nav(){
        
    }

}