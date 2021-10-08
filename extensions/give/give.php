<?php

if ( class_exists( 'Give' ) ) {

	define( 'IBX_WPFOMO_GIVE_DIR', IBX_WPFOMO_DIR . 'extensions/give/' );
	define( 'IBX_WPFOMO_GIVE_URL', IBX_WPFOMO_URL . 'extensions/give/' );

	require_once IBX_WPFOMO_GIVE_DIR . 'classes/class-ibx-wpfomo-give.php';
}
