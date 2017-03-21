<?php
    @header_remove( 'Last-Modified' );
    header("HTTP/1.1 404 Not Found");  
    header("Status: 404 Not Found");  
    header("Cache-Control: no-cache");
    header("Pragma: no-cache");
    echo '<h1>404</h1>';