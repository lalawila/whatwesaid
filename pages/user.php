<?php
global $user;
if( !$user->is_user_page() ) {
    page_404();
    exit();
}
if( isset($_FILES['avatar'])): 
    echo move_uploaded_file( $_FILES["avatar"]["tmp_name"],
       ABSPATH . "/content/image/avatar/" . $user->page_user->user_login . "/avatar.jpg" );

endif;

get_header();?>

<main id="main" class="site-main" role="main">
        <div  class="user">
            <div class="data">
                <div class="avatar">
                    <form action="/user/5" method="post" enctype="multipart/form-data" onchange="this.submit()">
                        <input type="file" name="avatar"  accept="image/png, image/jpeg" /> 
                    </form>
                    <p>Upload avatar</p>
                    <img src="/content/image/avatar/<?php echo $user->page_user->user_login ?>/avatar.jpg"/>
                </div>
                <div class="nick-name">
                <?php echo $user->page_user->nick_name;?>
                </div>
            </div>
        </div>
</main><!-- .site-main -->
