<?php

if ( class_exists( 'GFForms' ) ) {

	define( 'IBX_WPFOMO_GFORMS_DIR', IBX_WPFOMO_DIR . 'extensions/gravityforms/' );
	define( 'IBX_WPFOMO_GFORMS_URL', IBX_WPFOMO_URL . 'extensions/gravityforms/' );

	add_action( 'init', function() {
		if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
			require_once IBX_WPFOMO_GFORMS_DIR . 'classes/class-ibx-wpfomo-gforms.php';
		}
	} );
}
