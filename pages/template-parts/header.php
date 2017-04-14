<?php global $user ?>
<!DOCTYPE html>
<html lang="<?php echo get_lang() ?>" class="no-js">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
    <script type="text/javascript" src="https://cdn.mathjax.org/mathjax/2.6-latest/MathJax.js?config=TeX-AMS_HTML"></script>
    <!--<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>-->
	<?php 
	load_style('style.css');
    load_script('script.js');
    if( $func != null )
        $func();
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
                            <li><a href="/login?redirect_to=<?php echo base64_encode($_SERVER['REQUEST_URI']) ?>"><?php echo __('Login');?></a></li>
                            <li><a href="/login?action=register"><?php _e('Sign up');?></a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div><!-- .site-header-main -->
        </header><!-- .site-header -->
