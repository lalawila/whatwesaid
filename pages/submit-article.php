<?php 
global $user;
if( !$user->has_logined )
    header("Location: /login?msg=" . base64_encode(__('please login first')), true, '303');

get_header(); 
if( empty($_POST['title']) || empty($_POST['author']) || empty($_POST['article'])|| empty($_POST['lang']) 
    || ( empty($_POST['is_author']) && empty($_POST['original'] ))):
?>
<main id="main" class="site-main" role="main">
<form class ="submit-article-form" name="sumbit-article-form" id="sumbit-article-form" action="/submit-article" method="post">
<div class="posts">
<div class="submit-article-left">
	<input type="text" name="title" id="article-title"  placeholder="title" ></input>
	<input type="text" name="author" id="article-author"  placeholder="author"></input>
	<textarea id = "article" name = "article"  placeholder="article" ></textarea>
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
		CKEDITOR.replace( 'article', {filebrowserBrowseUrl: '/browser/browse.php',filebrowserImageBrowseUrl: '/browser/browse.php?type=Images',filebrowserUploadUrl: '/uploader/upload.php',filebrowserImageUploadUrl: '/uploader/upload.php?type=Images'});
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
    $original = isset($is_author)? null : $_POST['original'];
	if ( !in_array($lang, ["en", "zh"])) {
		$lang = "en";
	}
    $excerpt = strip_tags($article);
    $excerpt = mb_substr( $excerpt, 0, 200, "UTF-8") . '...';  
	global $wsdb;
    $data =['is_author' => $is_author, 'original' => $original, 'user_id' => $user->loged_user->ID,
        'lang' => $lang, $lang . '_title' => $title, $lang . '_content' => $article, $lang . '_excerpt' => $excerpt];
	
    if($wsdb->insert('articles', $data)){
        $ID = $wsdb->insert_id;
        header("Location: article/$ID", true, '303');
        exit();
    }
    WS_Error::print_error();
endif;
