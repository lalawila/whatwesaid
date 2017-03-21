<?php get_header(); ?>
    <main id="main" class="site-main" role="main">
        <div  class="posts">
 	      <?php
                $lang = $GLOBALS['lang'];
			    $manage = $GLOBALS['manage'];
                $article=$manage->the_article();
                if($article['en']!=NULL&&$lang=='en'){
                    $ID=$article['en'];
                }
                else if($article['zh']!=NULL&&$lang=='zh'){
                    $ID=$article['zh'];
                }
                else{
                    return;
                }  
                $post=$manage->get_post($lang,$ID);
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