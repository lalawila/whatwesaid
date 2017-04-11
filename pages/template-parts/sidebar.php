<?php
global $article;
$en = null;
$zh = null;
if ($GLOBALS['lang'] == 'zh'){
    $zh='selected';
}
else{
    $en='selected';
}
?>
<div id="secondary" class="siderbar" role="complementary">
	
<form>
<select class="language" name="language" onchange="window.location.href=this.options[selectedIndex].value" >
    <option value="<?php echo en_url?>" <?php echo $en?> >English</option>
    <option value="<?php echo zh_url?>" <?php echo $zh?> >中文</option>
</select>
</form>

<a class="button new-article" href="/submit-article" role="button" >new article</a>

<?php
if($article->is_article()):
?>
<label><?php echo _e('Author:'); ?></label>
<?php
if(!$article->is_author):
echo $article->authors[0]->name; 
endif;
?>

</div><!-- #secondary -->
