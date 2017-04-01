<?php get_header(); ?>
    <main id="main" class="site-main" role="main">
        <div  class="posts">
 	      <?php
		global $article;
                $posts = $article->get_article_from_newest();
                foreach ( $posts as $post ) 	
                {
                    ?>
                    <a class="loop-title" href="article/<?php echo $post['ID']; ?>"><?php echo $post['title']; ?></a>
                    <div class="loop-content"><?php echo $post['content']; ?></div>
                    <?php
                }
           ?>
        </div>
        <?php get_sidebar(); ?>
    </main><!-- .site-main -->

<?php //get_footer(); ?>
