<?php

define( 'IBX_WPFOMO_IMPORT_CSV_DIR', IBX_WPFOMO_DIR . 'extensions/import-csv/' );
define( 'IBX_WPFOMO_IMPORT_CSV_URL', IBX_WPFOMO_URL . 'extensions/import-csv/' );

add_action( 'init', function() {
	if ( class_exists( 'IBX_WPFomo_Addon' ) ) {
		require_once IBX_WPFOMO_IMPORT_CSV_DIR . 'classes/class-ibx-wpfomo-import-csv.php';
	}
} );
