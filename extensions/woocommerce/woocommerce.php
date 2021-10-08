<?php

if ( class_exists( 'WooCommerce' ) ) {

	define( 'IBX_WPFOMO_WOOCOMMERCE_DIR', IBX_WPFOMO_DIR . 'extensions/woocommerce/' );
	define( 'IBX_WPFOMO_WOOCOMMERCE_URL', IBX_WPFOMO_URL . 'extensions/woocommerce/' );

	require_once IBX_WPFOMO_WOOCOMMERCE_DIR . 'classes/class-ibx-wpfomo-woo.php';
}
