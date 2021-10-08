<?php

define( 'IBX_WPFOMO_ENVATO_DIR', IBX_WPFOMO_DIR . 'extensions/envato/' );
define( 'IBX_WPFOMO_ENVATO_URL', IBX_WPFOMO_URL . 'extensions/envato/' );

add_action(
	'init',
	function() {
		if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
			require_once IBX_WPFOMO_ENVATO_DIR . 'classes/class-ibx-wpfomo-envato.php';
		}
	}
);
