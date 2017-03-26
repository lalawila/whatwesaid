<?php get_header(); ?>
    <main id="main" class="site-main" role="main">
        <div  class="posts">
		<?php
		global $lang;
		global $article;
                $current_article=$article->the_article();
                if($current_article['en']!=NULL&&$lang=='en'){
                    $ID=$current_article['en'];
                }
                else if($current_article['zh']!=NULL&&$lang=='zh'){
                    $ID=$current_article['zh'];
                }
                else{
                    return;
                }  
                $post=$article->get_post($lang,$ID);
                ?>
                <h1 class="title"><?php echo $post['post_title']; ?></h1>
                <div class="content"><?php echo $post['post_content']; ?></div>
                <div class="comment-area">
                
                    <form>
                        <textarea class="comment" name="comment-1" id="comment-1" maxlength="140" placeholder="What do you thing?"></textarea>
                        <input type="submit" name="submit" id="submit" class="button button-comment" value="submit">
                    </form>
                </div>
        </div>
        <?php get_sidebar(); ?>
    </main><!-- .site-main -->

<?php //get_footer(); ?>
