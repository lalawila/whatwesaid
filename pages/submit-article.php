<?php 
global $user;
global $splited_uri;
global $article;
if( !$user->has_logined ){
    header("Location: /login?msg=" . base64_encode(__('please login first')), true, '303');
    exit();
}
if( count($splited_uri) != 1 && count($splited_uri) != 2)
    page_404();

$post = false;
if( count($splited_uri) == 2 ){
    if( !is_numeric( $splited_uri[1] ) || $splited_uri <= 0 )
        page_404();

    $post = $GLOBALS['article']->get_article( $splited_uri[1] );
    if( $post == false )
        page_404();

    if( $post->user_id != $user->loged_user->ID )
        page_404();

}

function get( $post, $name ){
    if( $post != false ):
        if($name == 'authors'){
            $authors_str = "";
            foreach( $post->authors as $author ) {
                $authors_str .= ';  ' . $author->name;
            }
            return substr($authors_str,3);
        }
        return $post->$name;
    endif;
    return '';
}


function load_file() {
    load_script('verify.js');
    load_script('detect-lang-ajax.js');
    load_ckeditor();

}
get_header('load_file'); 
if( empty($_POST['title']) || empty($_POST['content'])|| empty($_POST['lang'] || empty($_POST['author'])) 
    || ( empty($_POST['is_author']) && empty($_POST['original'] ))):
?>
<main id="main" class="site-main" role="main">
<form class ="submit-article-form" name="sumbit-article-form" id="sumbit-article-form" action="" method="post">
<div class="posts">
<div class="submit-article-left">
<input type="text" name="title" id="article-title"  placeholder="title" value="<?php echo get($post, 'title') ?>" ></input>
<input type="text" name="author" id="article-author"  placeholder="authors' name splited with ;" value="<?php echo get($post, 'authors') ?>"></input>
<textarea id = "content" name = "content"  placeholder="content" ><?php echo get( $post, 'content') ?></textarea>
    <div class = "language-submit">
    <div class="language">
        <p>the language is </p>
	    <select id="lang" name = "lang"> 
	        <option value = "en" <?php echo get($post,'lang') == 'en' ? 'selected':'' ?> >english</option>
	        <option value = "zh" <?php echo get($post,'lang') == 'zh' ? 'selected':'' ?> >chinese</option>
	    </select>
    </div>
	    <button id="submit-article" name = "submit-ariticle">submit</button>
    </div>
	<script>
		CKEDITOR.replace( 'content');
	</script>
</div>
</div>
<div class = "siderbar submit-article-right">
    <div class = "original">
        <input type="text" id="original" name="original" placeholder="The link to original" value="<?php echo get($post, 'original') ?>" ></input>
        <div class="is-original" >
        <input type="checkbox" id="is_author" name="is_author" <?php echo get($post,'is_author')?'checked':'' ?> onchange="document.getElementById('original').disabled=this.checked" ></input>
            <label for="is_author">I am the author.</label>
        </div>
    </div>
</div>
</form>
</main>
<?php
get_footer();
else:
	$title    = $_POST['title'];
	$author   = $_POST['author'];
	$lang     = $_POST['lang'];
    $content  = $_POST['content'];
    $is_author= isset($_POST['is_author']) ? true : false;
    $original = $_POST['original'];
	if ( !in_array($lang, ["en", "zh"])) {
		$lang = "en";
    }


    $excerpt = strip_tags($content);
    if( $lang == 'zh' )
        $excerpt = mb_substr( $excerpt, 0, 200, "UTF-8") . '...';  
    else 
        $excerpt = mb_substr( $excerpt, 0, 400, "UTF-8") . '...';  

    $authors = explode(';', $_POST['author']);
    $data =['is_author' => $is_author, 'original' => $original, 'user_id' => $user->loged_user->ID,
        'lang' => $lang, $lang . '_title' => $title, $lang . '_content' => $content, $lang . '_excerpt' => $excerpt, 'authors'=> $authors];

    if( count( $splited_uri ) == 2 ) {
        if( $post->user_id != $user->loged_user->ID )
            exit();

        $article->update_article( $post->ID, $data );
        header("Location: /article/$post->ID", true, '303');
        exit();
    }
    elseif ($ID = $article->insert_article($data) ){
        header("Location: /article/$ID", true, '303');
        exit();
    }
    WS_Error::print_error();
    echo $wsdb->last_error;
endif;
