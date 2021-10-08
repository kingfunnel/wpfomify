<?php

if ( class_exists( 'Easy_Digital_Downloads' ) ) {

	define( 'IBX_WPFOMO_EDD_DIR', IBX_WPFOMO_DIR . 'extensions/edd/' );
	define( 'IBX_WPFOMO_EDD_URL', IBX_WPFOMO_URL . 'extensions/edd/' );

	require_once IBX_WPFOMO_EDD_DIR . 'classes/class-ibx-wpfomo-edd.php';
}
