<?php
class WWS {
    private $manage;
    
    public function __construct() {
        $this->manage = $GLOBALS['manage'];
    }
    public function is_article(){
        return $this->manage->is_article();
    }
        
        
        
        
}