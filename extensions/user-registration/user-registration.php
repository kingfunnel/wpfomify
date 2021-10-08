<?php

define( 'IBX_WPFOMO_USER_REGISTRATION_DIR', IBX_WPFOMO_DIR . 'extensions/user-registration/' );
define( 'IBX_WPFOMO_USER_REGISTRATION_URL', IBX_WPFOMO_URL . 'extensions/user-registration/' );

add_action( 'init', function() {
	if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
		require_once IBX_WPFOMO_USER_REGISTRATION_DIR . 'classes/class-ibx-wpfomo-user-registration.php';
	}
} );
