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
        if (in_array( $name, ['title', 'content', 'excerpt']))
            return $this->article[ $this->article['lang'] . '_' . $name ];
        else
            return $this->article[$name];
    }

}
final class WS_ArticleManage {
    private $_page_article = null;
    private $_is_article = false;
    private $_db = null;
    private static $_single = null;
    private function __construct( $db = Null ) {
        global $splited_uri;

        if (isset($db) && $db instanceof WS_DB)
            $this->_db = $db;
        else
            $this->_db = $GLOBALS['wsdb'];

        if( count($splited_uri) == 2 && $splited_uri[0] == "article" && is_numeric($splited_uri[1]) && $splited_uri[1] > 0 ) {
            $this->_is_article = true;
            $this->_page_article = $this->get_article( $splited_uri[1] );
            if($this->_page_article == false)
                $this->_is_article = false;
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
        return $this->_page_article->$name;
    }

    public function get_article( $ID, $field = Null) {
        $field = isset($field) ? $field : '*';
        $article = $this->_db->get_row("
    	SELECT $field 
    	FROM articles
        WHERE ID=$ID
    	", ARRAY_A);
        
        if( $article == null )
            return false;

        $article['authors'] = $this->get_authors($ID);
        return new WS_Article( $article );
    }

    public function update_article( $ID, $data ) {
        $names = $data['authors'];
        unset($data['authors']);

        //print_r($data);
        $this->_db->delete('term_rel', ['rel' => 'article_author', 'ID_1' => $ID], ['%s', '%d']);
        $this->link_article_names_rel( $ID, $names );
        $this->_db->update('articles', $data, ['ID' => $ID], null ,[ 'ID'=>'%d']);
        return true;
    }

    public function link_article_names_rel ( $article_id, $names ) {
        foreach ( $names as $name ) {
            $name = trim($name);
            $name = strtolower($name);
            $name = ucwords($name);
            $author = $this->_db->get_row("
            SELECT ID
            FROM authors
            WHERE name='$name'
            ");
            if( $author == null ){
                $this->_db->insert('authors', ['name' => $name]);
                $author_id = $this->_db->insert_id;
            }
            else
                $author_id = $author->ID;

            $this->_db->insert('term_rel', ['rel' => 'article_author', 'ID_1' => $article_id, 'ID_2' => $author_id], ['%s', '%d','%d']);
        }
    }
    public function insert_article( $data ) {
        $names = $data['authors'];
        unset($data['authors']);
        $article_id = false;
        if( !$this->_db->insert('articles', $data))
            return false;
        $article_id = $this->_db->insert_id;

        $this->link_article_names_rel( $article_id, $names);

        return $article_id;    
    }



    public function get_authors($ID) {
        $authors = array();
        $author_ID = $this->_db->get_results("
    	SELECT ID_2
    	FROM term_rel
        WHERE ID_1=$ID AND rel='article_author' 
    	", ARRAY_A);
        $i = 0;
        foreach ($author_ID as $s_id) {
            $t = $s_id['ID_2'];
            $authors[$i] = $this->_db->get_row("
    	    SELECT *
    	    FROM authors
            WHERE ID=$t
    	    ");
            $i++;
        }
        return $authors;
    }

    public function get_articles_by_user( $ID ){
        $temps = $this->_db->get_results("
        SELECT *
        FROM articles
        WHERE user_id=$ID
        order by ID desc
        ", ARRAY_A);
        if($temps == null)
            return false;
        return $this->_init_articles($temps); 
    }
    private function _init_articles( $arr ) {
        if( $arr == null)
            return false;
        foreach( $arr as $at) {
            $articles[] = new WS_Article($at);
        }
        return $articles;
    }
    public function get_article_from_newest( $num = 5, $lang = "ori" ) {
        if( $lang == "ori" ):	
            $temps = $this->_db->get_results("
            SELECT *
            FROM articles
            order by ID desc limit $num
            ", ARRAY_A);
        else:
            $temps = $this->_db->get_results("
            SELECT * 
            FROM articles
            WHERE lang=$lang
            order by ID desc limit $num
            ", ARRAY_A);
        endif;
        return $this->_init_articles($temps); 
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
