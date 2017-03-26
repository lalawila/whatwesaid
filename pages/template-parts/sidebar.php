<?php
if ($GLOBALS['lang'] == 'zh'){
    $zh='selected';
}
else{
    $en='selected';
}
global $article;
?>
<div id="secondary" class="siderbar" role="complementary">
	
<form>
<select class="language" name="language" onchange="window.location.href=this.options[selectedIndex].value" >
    <option value="https://whatwesaid.xyz<?php echo $_SERVER['REQUEST_URI']?>" <?php echo $en?> >English</option>
    <option value="https://zh.whatwesaid.xyz<?php echo $_SERVER['REQUEST_URI']?>" <?php echo $zh?> >中文</option>
</select>
</form>
<?php
if($article->is_article()):
?>
<label><?php echo _e('Author:'); ?></label>
<?php
echo $article->the_article()['author'][0]['name'];
endif;
?>

</div><!-- #secondary -->
