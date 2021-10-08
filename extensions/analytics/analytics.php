<?php

define( 'IBX_WPFOMO_ANALYTICS_DIR', IBX_WPFOMO_DIR . 'extensions/analytics/' );
define( 'IBX_WPFOMO_ANALYTICS_URL', IBX_WPFOMO_URL . 'extensions/analytics/' );

add_action( 'init', function() {
	if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
		require_once IBX_WPFOMO_ANALYTICS_DIR . 'classes/class-ibx-wpfomo-analytics.php';
	}
} );
