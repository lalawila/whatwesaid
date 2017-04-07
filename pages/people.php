<?php
global $sprited_uri;
$ID = $sprited_uri[1]
$user_data = WS_User::get_user_data(['ID' => $ID]);
get_header(); ?>
<main id="main" class="site-main" role="main">
        <div  class="posts">
            <?php echo $user_data['nick_name'];?>
        </div>
</main><!-- .site-main -->
