<?php

if ( class_exists( 'SFWD_LMS' ) ) {

	define( 'IBX_WPFOMO_LEARNDASH_DIR', IBX_WPFOMO_DIR . 'extensions/learndash/' );
	define( 'IBX_WPFOMO_LEARNDASH_URL', IBX_WPFOMO_URL . 'extensions/learndash/' );

	require_once IBX_WPFOMO_LEARNDASH_DIR . 'classes/class-ibx-wpfomo-learndash.php';
}
