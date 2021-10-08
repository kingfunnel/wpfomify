<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// AJAX handler.
class MetaBox_Tabs_Ajax {
	static public function init() {
		add_action( 'wp_ajax_mbt_get_object_taxonomies', __CLASS__ . '::get_object_taxonomies' );
	}

	static public function get_object_taxonomies() {
		$taxonomies = Metabox_Tabs_Helper::taxonomies( $_POST['mbt_post_type'], $_POST['mbt_tax_exclude'] );

		$html = '';

		foreach ( $taxonomies as $tax_slug => $tax ) {
			$html .= '<option value="' . $tax_slug . '">' . $tax->label . '</option>';
		}

		echo $html;
		die();
	}
}
MetaBox_Tabs_Ajax::init();
