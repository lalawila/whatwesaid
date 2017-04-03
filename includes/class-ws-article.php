<?php
final class WS_Article {
    protected $_article = null;
    private $_is_article = false;
    private $sql = null;
    public function __construct( $db = Null ) {
        global $splited_uri;

        if (isset($db) && $db instanceof WS_DB)
            $this->sql = $db;
        else
            $this->sql = $GLOBALS['wsdb'];

        if( count($splited_uri) == 2 && $splited_uri[0] == "article" && is_numeric($splited_uri[1]) && $splited_uri[1] > 0 ) {
            $this->_is_article = true;
            $this->_article = $this->get_article( $splited_uri[1] );
        }
        
    }

    public function is_article() {
        return $this->_is_article;
    }
    public function the_article() {
        return $this->_article;
    }
    
    public function __get( $name ) {

        if(in_array( $name, ['title', 'content'])) 
            return $this->_article[$this->_article['lang'] . '_' . $name];
        if( in_array( $name, ["ID", "en_title", "en_content", "zh_title", "zh_content", "authors"]))
            return $this->_article[$name];

    }

    public function get_article( $ID, $field = Null) {
        $article = $this->sql->get_results("
    	SELECT ID, lang,original, date, en_title, zh_title, en_content, zh_content
    	FROM articles
        WHERE ID=$ID
    	", ARRAY_A);
        $article[0]['author'] = $this->get_authors($ID);
        return $article[0];
    }
    public function get_authors($ID) {

        $author_ID = $this->sql->get_results("
    	SELECT author_ID
    	FROM term_rel
        WHERE article_ID=$ID
    	", ARRAY_A);

        $i = 0;
        foreach ($author_ID as $s_id) {
            $t = $s_id['author_ID'];
            $authors[$i] = $this->sql->get_results("
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
        	$ats_temp = $this->sql->get_results("
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
        	$articles = $this->sql->get_results("
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
        $en_post = $this->sql->get_results("
    	SELECT ID, article_ID, post_title, post_content
    	FROM $tablename
        WHERE ID=$ID
    	", ARRAY_A);
        return $en_post[0];
    }
}
