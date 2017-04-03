<?php 
@header_remove('Last-Modified');
header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Content-Type:application/json;charset=utf-8;');
if(is_null($_POST['action']))
    exit('no action');
switch ($_POST){
    case 'comment':
        global $comment;
        if( $comment->add_comment($_POST['content'])){
            $json = ['status'=> 'OK'];
            echo json_encode($json);
        }   
        break;
}
