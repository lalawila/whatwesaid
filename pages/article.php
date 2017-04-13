<?php
global $article;
if( !$article->is_article())
    page_404();
$zh_css = 'display:none';
$en_css = 'display:none';
if( $article->lang == 'zh' ):
    $zh_css = 'display:block';
else:
    $en_css = 'display:block';
endif;

function translate() {
    echo '<div class="submit-translate"><p>no translate</p><a href="/translate/' . $article->ID .'">translate</a></div>';
}

get_header(); ?>
    <main id="main" class="site-main" role="main">
        <div class="posts" >
        <div id="post-en" style="<?php echo $en_css ?>">
                <h1 id="title-en" class="title" ><?php echo is_null($article->en_title)?$article->title:$article->en_title; ?></h1>
                <div class="select-lang">
                <label name="select-en" class="selected" onclick="select_en()"><?php echo __('English') ?></label>
                <label name="select-zh" class="unselect" onclick="select_zh()"><?php echo __('Chinese') ?></label>
                </div>
                <div id="content-en" class="content"><?php echo is_null($article->en_content)?tranlate():$article->en_content; ?></div>
        </div>
        <div id="post-zh" style="<?php echo $zh_css ?>">
                <h1 id="title-zh" class="title"><?php echo is_null($article->zh_title)?$article->title:$article->zh_title; ?></h1>
                <div class="select-lang">
                <label name="select-en" class="unselect" onclick="select_en()"><?php echo __('English') ?></label>
                <label name="select-zh" class="selected" onclick="select_zh()"><?php echo __('Chinese') ?></label>
                </div>
                <div id="content-zh" class="content"><?php echo is_null($article->zh_content)?translate():$article->zh_content; ?></div>
        </div>
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
