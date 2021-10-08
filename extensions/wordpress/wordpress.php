<?php

define( 'IBX_WPFOMO_WORDPRESS_DIR', IBX_WPFOMO_DIR . 'extensions/wordpress/' );
define( 'IBX_WPFOMO_WORDPRESS_URL', IBX_WPFOMO_URL . 'extensions/wordpress/' );

add_action( 'init', function() {
	if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
		require_once IBX_WPFOMO_WORDPRESS_DIR . 'classes/class-ibx-wpfomo-wordpress.php';
	}
} );
