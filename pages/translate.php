<?php 
global $user;
if( !$user->has_logined )
    header("Location: /login?msg=" . base64_encode(__('please login first')), true, '303');
function load_file() {
    load_ckeditor();

}
get_header('load_file'); 
if( empty($_POST['title']) || empty($_POST['author']) || empty($_POST['article'])|| empty($_POST['lang']) 
    || ( empty($_POST['is_author']) && empty($_POST['original'] ))):
?>
<main id="main" class="site-main" role="main">
<div class="translate">
<div class="translate-left">

</div>
<div class="translate-right">
<form class ="translate-form" id="translate-form" action="/translate" method="post">
	<input type="text" name="title" id="article-title"  placeholder="title" ></input>
	<textarea id = "article" name = "article"  placeholder="article" ></textarea>
    <div class = "language-submit">
    <div class="language">
	    <button name = "submit">submit</button>
	<script>
		CKEDITOR.replace( 'article', {filebrowserBrowseUrl: '/browser/browse.php',filebrowserImageBrowseUrl: '/browser/browse.php?type=Images',filebrowserUploadUrl: '/uploader/upload.php',filebrowserImageUploadUrl: '/uploader/upload.php?type=Images'});
	</script>
</form>
</div>
</div>
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
