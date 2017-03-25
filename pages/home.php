<?php get_header(); ?>
    <main id="main" class="site-main" role="main">
        <div  class="posts">
 	      <?php
			     $manage = $GLOBALS['article'];
                 $posts=$manage->get_all_posts('en');
                foreach ( $posts as $post ) 
                {
                    ?>
                    <a class="loop-title" href="article/<?php echo $post['article_ID']; ?>"><?php echo $post['post_title']; ?></a>
                    <div class="loop-content"><?php echo $post['post_excerpt']; ?></div>
                    <?php
                }
           ?>
        </div>
        <?php get_sidebar(); ?>
    </main><!-- .site-main -->

<?php //get_footer(); ?>
