<?php
$ID = explode('/', $_SERVER['REDIRECT_URL'])[2];
if(is_numeric($ID) && $ID > 0):
    $user_data = WS_User::get_user_data(['ID' => $ID]);
    if($user_data):
        get_header(); ?>
        <main id="main" class="site-main" role="main">
            <div  class="posts">
                <?php echo $user_data['nick_name'];?>
            </div>
        </main><!-- .site-main -->
<?php else:
        page_404();
    endif;
endif;

    