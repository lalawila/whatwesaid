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

function translate($lang) {
    echo '<div class="submit-translate"><p>no traslation</p><a href="/translate/' . $GLOBALS['article']->ID . '?lang=' . $lang .'">translate</a></div>';
}
function load_comment() {
    load_script('comment-ajax.js');
}
get_header('load_comment'); ?>
    <main id="main" class="site-main" role="main">
    <div class = "article">
        <div id = "primary" class="main" >
        <div id="post-en" style="<?php echo $en_css ?>">
                <h1 id="title-en" class="title" ><?php echo is_null($article->en_title)?$article->title:$article->en_title; ?></h1>
                <div class="select-lang">
                <label name="select-en" class="selected" onclick="select_en()"><?php echo __('English') ?></label>
                <label name="select-zh" class="unselect" onclick="select_zh()"><?php echo __('Chinese') ?></label>
                </div>
                <div id="content-en" class="content"><?php echo is_null($article->en_content)?tranlate('en'):$article->en_content; ?></div>
        </div>
        <div id="post-zh" style="<?php echo $zh_css ?>">
                <h1 id="title-zh" class="title"><?php echo is_null($article->zh_title)?$article->title:$article->zh_title; ?></h1>
                <div class="select-lang">
                <label name="select-en" class="unselect" onclick="select_en()"><?php echo __('English') ?></label>
                <label name="select-zh" class="selected" onclick="select_zh()"><?php echo __('Chinese') ?></label>
                </div>
                <div id="content-zh" class="content"><?php echo is_null($article->zh_content)?translate('zh'):$article->zh_content; ?></div>
        </div>
        <form id="comment-area" class="comment-area" >
                <input type="hidden" name="article" value="<?php echo $article->ID ?>" >
                <textarea class="comment" name="comment" id="comment" maxlength="140" placeholder="What do you thing?"></textarea>
                <input type="submit" name="submit" id="submit" class="button button-comment" value="submit">
                <div id = "inbox"> </div>
        </form>
        </div>
        <div id="secondary" class="siderbar" role="complementary">
            <?php
            global $article;
            echo '<a href="' . $article->original . '">Original</a></br>';
            if($article->is_author == false):
                ?><label><?php echo __('Author:'); ?></label><?php
                    foreach( $article->authors as $author)
                        echo $author->name;

            endif;
            ?>
        </div>
    </div>
    </main><!-- .site-main -->
<?php get_footer();
