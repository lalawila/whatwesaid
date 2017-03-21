<?php
$page = get_page_name();
switch ($page) {
    case 'article':
    case 'login':
    case 'people':
        require_once (ABSPATH . 'pages/' . $page . '.php');
        break;
    case '':
        require_once (ABSPATH . 'pages/home.php');
        break;
    default:
        page_404();
        break;
}
