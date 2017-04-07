<?php
global $user;
if( !$user->is_user_page() ) {
    page_404();
    exit();
}
get_header(); ?>
<main id="main" class="site-main" role="main">
        <div  class="posts">
            <?php echo $user->page_user->nick_name;?>
        </div>
</main><!-- .site-main -->
