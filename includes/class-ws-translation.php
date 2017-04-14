<?php
class WS_TranslationManage {
    public $_db;
    private $_single;
    public function __construct( $db = Null ) {
        global $splited_uri;

        if (isset($db) && $db instanceof WS_DB)
            $this->_db = $db;
        else
            $this->_db = $GLOBALS['wsdb'];

    }
    public static function instance( $db = null ) {
        return isset(WS_TranslationManage::$_single) ? WS_TranslationManage::$_single : new WS_TranslationManage( $db );
    } 

    public function get_translations_by_user( $ID, $field = null ){
        $field = isset($field) ? $field : '*';
        $translations = $this->_db->get_results("
        SELECT $field
        FROM  translation
        WHERE user_id=$ID
        ");

        if( $translations == null )
            return false;
        return $translations; 
    
    }


}
