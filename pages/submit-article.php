<?php get_header(); 
if( empty($_POST['title']) || empty($_POST['author']) || empty($_POST['article']) ):
?>
<main id="main" class="site-main" role="main"
<div class="submit-article">
    <form class="sumbit-article-form" name="sumbit-article-form" id="sumbit-article-form" action="/submit-article" method="post">
	<input type="text" name="title" id="article-title"  placeholder="article titlee"></input>
	<input type="text" name="author" id="article-author"  placeholder="article author"></input>
	<textarea id = "article" name = "article"  placeholder="article"></textarea>
	<select name = "lang">
	<option value = "en">english</option>
	<option value = "zh">chinese</option>
	</select>
	<button name = "submit">submit</button>
	<script>
		CKEDITOR.replace( 'article' );
	</script>
    </form>
</div>
</main>
<?php
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
