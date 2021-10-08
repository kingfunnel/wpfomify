<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for Custom Form data.
 *
 * @since 1.0.0
 */
class IBX_WPFomo_List_Helper {

	/**
	 * Holds Custom Form data.
	 *
	 * @var array|boolean $data
	 * @since 1.0.0
	 */
	public static $data = false;

	/**
	 * Create entries page URL for Show Entries button.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_button_url( $src = '' ) {
		$post_type = IBX_WPFomo_Admin::$type;
		if ( ! empty( $src ) ) {
			$src = '&src=' . $src;
		}
		return admin_url( '/edit.php?post_type=' . $post_type . '&page=wpfomo-custom-form-data' . $src . '&post=' . self::get_post_id() );
	}

	/**
	 * Get post id from the current URL.
	 *
	 * @since 1.0.0
	 * @return integer
	 */
	public static function get_post_id() {
		$post_id = 0;

		if ( isset( $_REQUEST['post'] ) ) {
			$post_id = $_REQUEST['post'];
		} elseif ( isset( $_REQUEST['post_id'] ) ) {
			$post_id = $_REQUEST['post_id'];
		}

		return absint( wp_unslash( $post_id ) );
	}

	/**
	 * Get Custom Form data from post meta and cache it.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_data() {
		$order_by       = '';
		$where_condition = '';

		if ( is_array( self::$data ) && count( self::$data ) ) {
			return self::$data;
		} else {
			$post_id = self::get_post_id();

			if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) && ! empty( $_GET['orderby'] ) && ! empty( $_GET['order'] ) ) {
				$order_by = ' ORDER BY ' . sanitize_text_field( $_GET['orderby'] ) . ' ' . sanitize_text_field( $_GET['order'] );
			}

			if ( isset( $_GET['src'] ) && 'csv' === sanitize_text_field( $_GET['src'] ) ) {
				$where_condition = ' AND src IN ("csv") ';
			} else {
				$where_condition = ' AND ( src IS NULL OR src NOT IN ("csv") ) ';
			}

			self::$data = IBX_WPFomo_Conversion::get_conversion_data( $post_id, $where_condition, $order_by, '', true );
		}

		if ( ! is_array( self::$data ) ) {
			return array();
		}

		return self::$data;
	}

	/**
	 * Get count of number of entries in data.
	 *
	 * @since 1.0.0
	 * @return integer
	 */
	public static function entries_count() {
		$post_id = self::get_post_id();

		if ( ! $post_id ) {
			return;
		}

		$data  = self::get_data();
		$count = 0;

		if ( ! is_array( $data ) ) {
			return $count;
		}

		$count = count( $data );

		return $count;
	}

	/**
	 * Get entries conditionally.
	 *
	 * @param int $per_page Number of entries to be shown on the page.
	 * @param int $page_number Number of the current page.
	 * @since 1.0.0
	 * @return array
	 */
	public static function get_entries( $per_page = 10, $page_number = 1 ) {
		$data = self::get_data();

		if ( ! count( $data ) ) {
			return $data;
		}

		$offset_data = array();
		$total_data  = count( $data );

		if ( 1 == $page_number ) {
			if ( $total_data >= $per_page ) {
				$offset_data = array_slice( $data, 0, $per_page, true );
			} else {
				$offset_data = $data;
			}
		}
		if ( $page_number > 1 ) {
			$offset = $per_page * ( $page_number - 1 );
			if ( $total_data >= $offset ) {
				$offset_data = array_slice( $data, $offset, $per_page, true );
			} else {
				$offset_data = array();
			}
		}
		return $offset_data;
	}

	/**
	 * Delete an entry from Custom Form data.
	 *
	 * @param int $entry_id ID of the entry to be deleted.
	 * @since 1.0.0
	 * @return void
	 */
	public static function delete_entry( $entry_id = 0 ) {
		if ( ! $entry_id ) {
			return;
		}
		IBX_WPFomo_Conversion::delete_conversion( $entry_id );
	}
}
