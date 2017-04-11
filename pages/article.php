<?php get_header(); ?>
    <main id="main" class="site-main" role="main">
        <div  class="posts">
		<?php global $article; ?>
                <h1 class="title"><?php echo $article->title; ?></h1>
                <div class="content"><?php echo $article->content; ?></div>
                <div class="comment-area">
                        <textarea class="comment" name="comment-1" id="comment-1" maxlength="140" placeholder="What do you thing?"></textarea>
                        <input type="submit" name="submit" id="submit" class="button button-comment" value="submit">
                </div>
        </div>
        <div id="secondary" class="siderbar" role="complementary">
            <?php
            global $article;
            if($article->is_author == false):
                ?><label><?php echo __('Author:'); ?></label><?php
                //echo $article->authors[0]->name;
            endif;
            ?>
        </div>
    </main><!-- .site-main -->
<?php get_footer();
