<!DOCTYPE html>
<html lang="<?php echo get_lang() ?>" class="no-js">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php 
	load_style('style.css');

        if($GLOBALS['page'] === 'submit-article'):
		WS_Comment::load_ckeditor(); 
		load_script('detect-lang-ajax.js');
	endif;
    ?>
</head>

<body>
<div id="page" class="site">
    <div class="site-inner">
        <header id="masthead" class="site-header" role="banner">
         <div class="site-header-main">
                <nav class="primary-navigation" role="navigation" aria-label="Primary Navigation">
               
                    <ul id="menu-wws" class="menu">
                        <li><a href="/"><?php _e('Home');?></a></li>
                    </ul>
                    <ul id="menu-user" class="menu">
                        <?php 
                            if(WS_User::check_login()): 
                                global $user;
                        ?>
                            <li><a href="/people/<?php echo $user->data['ID'] ?>"><?php echo __('Hi,' ). $user->data['nick_name'];?></a></li>
                            <li><a href="/login?action=logout&redirect_to=<?php echo $_SERVER['REDIRECT_URL'] ?>"><?php echo __('logout');?></a></li>
                        <?php else:?>
                            <li><a href="/login?redirect_to=<?php echo $_SERVER['REDIRECT_URL'] ?>"><?php _e('Login');?></a></li>
                            <li><a href="/login?action=register"><?php _e('Sign up');?></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div><!-- .site-header-main -->
        </header><!-- .site-header -->
