<?php

define( 'IBX_WPFOMO_CUSTOM_FORM_DIR', IBX_WPFOMO_DIR . 'extensions/custom-form/' );
define( 'IBX_WPFOMO_CUSTOM_FORM_URL', IBX_WPFOMO_URL . 'extensions/custom-form/' );

add_action( 'init', function() {
	if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
		require_once IBX_WPFOMO_CUSTOM_FORM_DIR . 'classes/class-ibx-wpfomo-custom-form.php';
	}
} );
