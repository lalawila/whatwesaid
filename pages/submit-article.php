<?php 
global $user;
if( !$user->has_logined )
    header("Location: /login?msg=" . base64_encode(__('please login first')), true, '303');

get_header(); 
if( empty($_POST['title']) || empty($_POST['author']) || empty($_POST['article']) ):
?>
<main id="main" class="site-main" role="main">
<div class="posts">
<div class="submit-article">
    <form class="submit-article-form" name="sumbit-article-form" id="sumbit-article-form" action="/submit-article" method="post">
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
    </form>
</div>
</div>
</main>
<?php
get_footer();
else:
	$title  = $_POST['title'];
	$author = $_POST['author'];
	$lang   = $_POST['lang'];
	$article= $_POST['article'];

	if ( !in_array($lang, ["en", "zh"])) {
		$lang = "en";
	}
	
	global $wsdb;
	$data =['lang' => $lang, $lang . '_title' => $title, $lang . '_article' => $article];
	

	$wsdb->insert('articles', $data);
endif;
