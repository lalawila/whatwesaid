<?php
class WWS {
    private $article;
    
    public function __construct() {
        $this->article = $GLOBALS['article'];
    }
    public function is_article(){
        return $this->article->is_article();
    }
        
        
        
        
}
