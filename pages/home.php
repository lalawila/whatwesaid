<?php get_header(); ?>
    <main id="main" class="site-main" role="main">
    <div class = "home">
        <div id="primary"  class="main">
 	      <?php
		global $article;
                $posts = $article->get_article_from_newest();
                foreach ( $posts as $post ) 	
                {
                    ?>
                    <a class="loop-title" href="article/<?php echo $post->ID; ?>"><?php echo $post->title; ?></a>
                    <div class="loop-content"><?php echo $post->excerpt; ?></div>
                    <?php
                }
           ?>
        </div>
        <div id="secondary" class="siderbar" role="complementary">
            <a class="button new-article" href="/submit-article" role="button" >new article</a>
        </div>
    </div>
    </main><!-- .site-main -->

<?php get_footer(); ?>
