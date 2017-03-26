<?php
global $page;
switch ($page) {
    case 'article':
    case 'login':
    case 'people':
    case 'submit-article':
        require_once (ABSPATH . 'pages/' . $page . '.php');
        break;
    case '':
        require_once (ABSPATH . 'pages/home.php');
        break;
    default:
        page_404();
        break;
}
