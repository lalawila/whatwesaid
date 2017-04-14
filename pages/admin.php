<?php
global $user;
global $wsdb;
if( !$user->has_logined )
    page_404();

if(!$user->loged_user->user_status & 0b100 )
    page_404();

get_header();?>
<main id="main" class="site-main" role="main">
<?php    
 $tls = $wsdb->get_results("
 SELECT *
 FROM translation
 ");
foreach( $tls as $tl ){
    echo $tl->ID;
}
?>
</main>
