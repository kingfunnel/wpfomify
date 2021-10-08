<?php

define( 'IBX_WPFOMO_FREEMIUS_DIR', IBX_WPFOMO_DIR . 'extensions/freemius/' );
define( 'IBX_WPFOMO_FREEMIUS_URL', IBX_WPFOMO_URL . 'extensions/freemius/' );

add_action( 'init', function () {
	if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
		require_once IBX_WPFOMO_FREEMIUS_DIR . 'classes/class-ibx-wpfomo-freemius.php';
	}
}, 10 );
