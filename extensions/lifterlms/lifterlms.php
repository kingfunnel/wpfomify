<?php

if ( class_exists( 'LifterLMS' ) ) {

	define( 'IBX_WPFOMO_LLMS_DIR', IBX_WPFOMO_DIR . 'extensions/lifterlms/' );
	define( 'IBX_WPFOMO_LLMS_URL', IBX_WPFOMO_URL . 'extensions/lifterlms/' );

	require_once IBX_WPFOMO_LLMS_DIR . 'classes/class-ibx-wpfomo-llms.php';
}
