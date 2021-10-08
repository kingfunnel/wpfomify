<?php

define( 'IBX_WPFOMO_GOOGLE_REVIEWS_DIR', IBX_WPFOMO_DIR . 'extensions/google-reviews/' );
define( 'IBX_WPFOMO_GOOGLE_REVIEWS_URL', IBX_WPFOMO_URL . 'extensions/google-reviews/' );

add_action( 'init', function() {
	if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
		require_once IBX_WPFOMO_GOOGLE_REVIEWS_DIR . 'classes/class-ibx-wpfomo-google-reviews.php';
	}
} );
