<?php
final class WS_Article {
    protected static $_instance = null;
    protected static $_article = null;
    private $sql = null;
    public function __construct() {
        $this->sql = $GLOBALS['wsdb'];
    }

    public function is_article() {
        $path = dirname($_SERVER['REQUEST_URI']);
        if ($path == '/article') {
            return true;
        }
        return false;
    }
    public function the_article() {
        if (!$this->is_article())
            return null;

        $ex = explode("/", $_SERVER['REQUEST_URI']);
        if (!is_null(self::$_article) && self::$_article['ID'] == $ex[2])
            return self::$_article;
        else {
            return self::$_article = $this->get_article($ex[2]);
        }
    }
    public function the_ID() {
        if (!$this->is_article())
            return null;
        return $this->the_article()['ID'];
    }

    public function get_article($ID) {
        if (!is_null(self::$_article) && self::$_article['ID'] == $ID)
            return self::$_article;

        $article = $this->sql->get_results("
    	SELECT ID, original, date, en, zh
    	FROM articles
        WHERE ID=$ID
    	", ARRAY_A);
        $article[0]['author'] = $this->get_author($ID);
        return $article[0];
    }
    public function get_author($ID) {

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

    public function get_all_posts($lang) {
        $tablename = $lang . '_posts';
        $posts = $this->sql->get_results("
    	SELECT ID, article_ID, post_title, post_excerpt
    	FROM $tablename
    	", ARRAY_A);
        return $posts;
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
