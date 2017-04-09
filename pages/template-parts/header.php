<?php global $user ?>
<!DOCTYPE html>
<html lang="<?php echo get_lang() ?>" class="no-js">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
    <script type="text/javascript" src="http://cdn.mathjax.org/mathjax/2.6-latest/MathJax.js?config=TeX-AMS_HTML"></script>
	<?php 
	load_style('style.css');

        if($GLOBALS['splited_uri'][0] === 'submit-article'):
		load_ckeditor(); 
		load_script('detect-lang-ajax.js');
	endif;
    ?>
</head>

<body>
        <header id="masthead" class="site-header" role="banner">
         <div class="site-header-main">
                <nav class="primary-navigation" role="navigation" aria-label="Primary Navigation">
               
                    <ul id="menu-wws" class="menu">
                        <li><a href="/"><?php _e('Home');?></a></li>
                    </ul>
                    <ul id="menu-user" class="menu">
                        <?php 
                            if($user->has_logined): 
                                global $user;
                        ?>
                            <li><a href="/user/<?php echo $user->loged_user->ID ?>"><?php echo __('Hi,' ). $user->loged_user->nick_name ?></a></li>
                            <li><a href="/login?action=logout&redirect_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>"><?php echo __('logout');?></a></li>
                        <?php else:?>
                            <li><a href="/login?redirect_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>"><?php _e('Login');?></a></li>
                            <li><a href="/login?action=register"><?php _e('Sign up');?></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div><!-- .site-header-main -->
        </header><!-- .site-header -->
