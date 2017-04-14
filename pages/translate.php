<?php 
global $user;
global $splited_uri;
if( !$user->has_logined )
    header("Location: /login?msg=" . base64_encode(__('please login first')), true, '303');

if( count($splited_uri) != 2)
    page_404();

$post = false;

if( !is_numeric( $splited_uri[1] ) || $splited_uri <= 0 )
    page_404();

$post = $GLOBALS['article']->get_article( $splited_uri[1] );
if( $post == false )
    page_404();

if( $post->user_id != $user->loged_user->ID )
    page_404();


function load_file() {
    load_ckeditor();

}
get_header('load_file'); 
if( empty($_POST['title']) || empty($_POST['content'])|| empty($_GET['lang']) ):
?>
<main id="main" class="site-main" role="main">
<div class="translate">
<div class="translate-left">
        <h1 class="title" ><?php echo $post->title; ?></h1>
        <div class="content"><?php echo $post->content; ?></div>
</div>
<div class="translate-right">
<form class ="translate-form" id="translate-form" action="" method="post">
	<input type="text" name="title" id="article-title"  placeholder="title" ></input>
	<textarea id = "article" name = "content"  placeholder="article" ></textarea>
	<button name = "submit">submit</button>
	<script>
		CKEDITOR.replace( 'article');
	</script>
</form>
</div>
</div>
</main>
<?php
get_footer();
else:
	$title    = $_POST['title'];
	$lang     = $_GET['lang'];
    $article  = $_POST['content'];

	if ( !in_array($lang, ["en", "zh"])) {
		$lang = "en";
	}

	global $wsdb;
    $data =['user_id' => $user->loged_user->ID, 'article_id' => $post->ID,
        'lang' => $lang, 'title' => $title, 'content' => $article ];
	
    if($wsdb->insert('translation', $data,['%d','%d','%s','%s','%s'] )){
        $ID = $user->loged_user->ID;
        header("Location: /user/$ID", true, '303');
        exit();
    }
    WS_Error::print_error();
endif;
