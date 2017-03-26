<?php get_header(); ?>
<main id="main" class="site-main" role="main"
<div class="submit-article">
    <form class="sumbit-article-form" name="sumbit-article-form" id="sumbit-article-form" action="<?php echo get_site_url() ?>/submit-article" method="post">
	<input type="text" name="title" id="article-title"  placeholder="article titlee"></input>
	<input type="text" name="author" id="article-author"  placeholder="article author"></input>
	<textarea id = "article" name = "article"  placeholder="article"></textarea>
	<button name = "submit">submit</button>
	<script>
		CKEDITOR.replace( 'article' );
	</script>
    </form>
</div>
</main>
