<?php
// ESI handler — loaded via Varnish ESI include to render the cached sidebar.
$path = dirname( __FILE__ );
$abspath = substr( $path, 0, strpos( $path, 'wp-content' ) );
require $abspath . 'wp-blog-header.php';
dynamic_sidebar( 'esi-widget-sidebar' );
