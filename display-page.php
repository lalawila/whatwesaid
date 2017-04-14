<?php
global $splited_uri;
switch ($splited_uri[0]) {
    case 'article':
    case 'login':
    case 'user':
    case 'submit-article':
    case 'translate':
    case 'admin':
        require_once (ABSPATH . 'pages/' . $splited_uri[0] . '.php');
        break;
    case 'home':
        require_once (ABSPATH . 'pages/home.php');
        break;
    default:
        page_404();
        break;
}
