<?php
global $user;
global $translation_m;
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
                    <form action="" method="post" enctype="multipart/form-data" onchange="this.submit()">
                        <input type="file" name="avatar"  accept="image/png, image/jpeg" /> 
                    </form>
                    <p>Upload avatar</p>
                    <img src="/content/image/avatar/<?php echo $user->page_user->user_login ?>/avatar.jpg"/>
                </div>
                <div class="nick-name">
                <?php echo $user->page_user->nick_name;?>
                </div>
            </div>
            <div class="list">
            <div class="record-list">
            <p> article </p>
                <?php 
                $posteds = $article->get_articles_by_user($user->page_user->ID);
                foreach( $posteds as $post ){
                    echo '<div class="record"> <a class="title-link" href="/article/' . $post->ID . '">' . $post->title . '</a>';
                    if($user->has_logined && $user->loged_user == $user->page_user)
                        echo '<a class="title-edit" href="/submit-article/' . $post->ID . '">[edit]</a>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="translation-list">
            <p> translation </p>
                <?php
                $translations = $translation_m->get_translations_by_user($user->page_user->ID);
                foreach( $translations as $translation ){
                    echo '<div class="record"> <a class="title-link" href="/article/' . $translation->ID . '">' . $translation->title . '</a>';
                    echo '[' . $translation->status . ']';
                }
                ?>
            </div>
            </div>
        </div>
</main><!-- .site-main -->
