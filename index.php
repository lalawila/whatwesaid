<?php
//echo phpinfo();

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__file__) . '/');
}

if($_SERVER['REQUEST_URI'] == '/deploy.php'):
    require (ABSPATH . 'deploy.php');
    exit();
endif;

require_once (ABSPATH . 'setting.php');
require_once (ABSPATH . 'display-page.php');
