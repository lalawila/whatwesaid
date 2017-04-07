<?php
class WS_Article {

    protected $article;
    private $_db;

    public function __construct( $article, $db = Null ) {
        $this->article = $article;
        if( isset($db) && $db instanceof WS_DB)
            $this->_db = $db;
        else
            $this->_db = $GLOBALS['wsdb'];
    }
    public function __get( $name ) {
        if (in_array( $name, ['title', 'content']))
            return $article[ $article['lang'] . '_' . $name ];
        else
            return $article[$name];
    
    }

}
final class WS_ArticleManage {
    private $_page_article = null;
    private $_is_article = false;
    private $_db = null;
    private static $_single = null;
    public function __construct( $db = Null ) {
        global $splited_uri;

        if (isset($db) && $db instanceof WS_DB)
            $this->_db = $db;
        else
            $this->_db = $GLOBALS['wsdb'];

        if( count($splited_uri) == 2 && $splited_uri[0] == "article" && is_numeric($splited_uri[1]) && $splited_uri[1] > 0 ) {
            $this->_is_article = true;
            $this->_article = $this->get_article( $splited_uri[1] );
        }
        
    }

    public static function instance( $db = null ) {
        return isset(WS_ArticleManage::$_single) ? WS_ArticleManage::$_single : new WS_ArticleManage( $db );

    }

    public function is_article() {
        return $this->_is_article;
    }
    public function the_article() {
        return $this->_page_article;
    }
    
    public function __get( $name ) {
            return $this->_page_article-> $name;
    }

    public function get_article( $ID, $field = Null) {
        $field = isset($field) ? $field : '*';
        $article = $this->_db->get_row("
    	SELECT $field
    	FROM articles
        WHERE ID=$ID
    	", ARRAY_A);
        $article[0]['author'] = $this->get_authors($ID);
        return $article[0];
    }
    public function get_authors($ID) {

        $author_ID = $this->_db->get_results("
    	SELECT author_ID
    	FROM term_rel
        WHERE article_ID=$ID
    	", ARRAY_A);

        $i = 0;
        foreach ($author_ID as $s_id) {
            $t = $s_id['author_ID'];
            $authors[$i] = $this->_db->get_results("
    	   SELECT ID, name
    	   FROM author
           WHERE ID=$t
    	   ", ARRAY_A)[0];
            $i++;
        }
        return $authors;
    }

    public function get_article_from_newest( $num = 5, $lang = "ori" ) {
	$title   = $lang . '_title';
	$content = $lang . '_content';
	if( $lang == "ori" ):	
        	$ats_temp = $this->_db->get_results("
    		SELECT ID, lang, date, en_title, en_content, zh_title, zh_content
    		FROM articles
		order by ID desc limit $num
		", ARRAY_A);
		foreach( $ats_temp as $at) {
			$at['title']   = $at[$at['lang'] . '_title'];
			$at['content'] = $at[$at['lang'] . '_content'];
			unset($at['en_title']);
			unset($at['zh_title']);
			unset($at['en_content']);
			unset($at['zh_content']);
			$articles[] = $at;
		} 
	else:
        	$articles = $this->_db->get_results("
    		SELECT ID, lang, date, $ltitle,$content 
    		FROM articles
		WHERE lang=$lang
		order by ID desc limit $num
		", ARRAY_A);
	endif;
        return $articles;
    }

    public function get_post($lang, $ID) {
        $tablename = $lang . '_posts';
        $en_post = $this->_db->get_results("
    	SELECT ID, article_ID, post_title, post_content
    	FROM $tablename
        WHERE ID=$ID
    	", ARRAY_A);
        return $en_post[0];
    }
}
