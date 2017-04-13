<?php 
global $user;
global $splited_uri;

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
            foreach( $post->authors as $author)
                $authors_str += $author . '; ';
            return $authors_str;
        }
        return $post->$name;
    endif;
    return '';
}


function load_file() {
    load_ckeditor();

}
get_header('load_file'); 
if( empty($_POST['title']) || empty($_POST['article'])|| empty($_POST['lang']) 
    || ( empty($_POST['is_author']) && empty($_POST['original'] ))):
?>
<main id="main" class="site-main" role="main">
<form class ="submit-article-form" name="sumbit-article-form" id="sumbit-article-form" action="" method="post">
<div class="posts">
<div class="submit-article-left">
<input type="text" name="title" id="article-title"  placeholder="title" value="<?php echo get($post, 'title') ?>" ></input>
<input type="text" name="author" id="article-author"  placeholder="author"><?php echo get($post, 'authors') ?></input>
<textarea id = "article" name = "article"  placeholder="article" ><?php echo get( $post, 'content') ?></textarea>
    <div class = "language-submit">
    <div class="language">
        <p>the language is </p>
	    <select id="lang" name = "lang"> 
	        <option value = "en">english</option>
	        <option value = "zh">chinese</option>
	    </select>
    </div>
	    <button name = "submit">submit</button>
    </div>
	<script>
		CKEDITOR.replace( 'article');
	</script>
</div>
</div>
<div class = "siderbar submit-article-right">
    <div class = "original">
        <input type="text" id="original" name="original" placeholder="The link to original"></input>
        <div class="is-original" >
            <input type="checkbox" name="is_author" onchange="document.getElementById('original').disabled=this.checked" ></input>
            <p>I am the author of this article.</p>
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
    $article  = $_POST['article'];
    $is_author= isset($_POST['is_author']) ? true : false;
    $original = isset($is_author) ? null : $_POST['original'];
	if ( !in_array($lang, ["en", "zh"])) {
		$lang = "en";
	}
    $excerpt = strip_tags($article);
    $excerpt = mb_substr( $excerpt, 0, 200, "UTF-8") . '...';  
	global $wsdb;
    $data =['is_author' => $is_author, 'original' => $original, 'user_id' => $user->loged_user->ID,
        'lang' => $lang, $lang . '_title' => $title, $lang . '_content' => $article, $lang . '_excerpt' => $excerpt];

    if( count( $splited_uri ) == 2 ) {
        if( $post->user_id != $user->loged_user->ID )
            exit();
        if( $wsdb->update('articles', $data, ['ID' => $post->ID], null ,[ 'ID'=>'%d'])){
            $ID = $wsdb->insert_id;
            header("Location: /article/$post->ID", true, '303');
            exit();
        }
    }
    elseif ($wsdb->insert('articles', $data)){
        $ID = $wsdb->insert_id;
        header("Location: /article/$ID", true, '303');
        exit();
    }
    WS_Error::print_error();
    echo $wsdb->last_error;
endif;
