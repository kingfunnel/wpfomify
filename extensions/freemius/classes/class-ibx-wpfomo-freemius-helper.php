<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for Freemius helper functions.
 *
 * @since 2.0.0
 */
class IBX_WPFomo_Freemius_Helper {
	/**
	 * Holds the message.
	 *
	 * @since 2.0
	 * @var string $fs_api_scope
	 */
	public static $fs_api_scope = 'store';

	/**
	 * Holds the message type.
	 *
	 * @since 2.0
	 * @var string $fs_api_store_id
	 */
	public static $fs_api_store_id = '';

	/**
	 * Holds the message type.
	 *
	 * @since 2.0
	 * @var string $fs_api_public_key
	 */
	public static $fs_api_public_key = '';

	/**
	 * Holds the message type.
	 *
	 * @since 2.0
	 * @var string $fs_api_secret_key
	 */
	public static $fs_api_secret_key = '';

	/**
	 * Holds the message type.
	 *
	 * @since 2.0
	 * @var string $fs_api_sandbox
	 */
	public static $fs_api_sandbox = false;

	/**
	 * Holds the message type.
	 *
	 * @since 2.0
	 * @var string $api
	 */
	public static $api;


	/**
	 * Load the plugin.
	 *
	 * @since 2.0
	 * @param string $fs_api_store_id
	 * @param string $fs_api_public_key
	 * @param string $fs_api_secret_key
	 * @return object
	 */
	public static function get_api( $fs_api_store_id = '', $fs_api_public_key = '', $fs_api_secret_key = '' ) {
		if ( self::$api ) {
			return self::$api;
		}
		if ( ! class_exists( 'Freemius_Api' ) ) {
			self::load_files();
		}

		if ( empty( $fs_api_store_id ) ) {
			$fs_api_store_id = IBX_WPFomo_Admin::get_settings( 'freemius_store_id' );
		}

		if ( empty( $fs_api_public_key ) ) {
			$fs_api_public_key = IBX_WPFomo_Admin::get_settings( 'freemius_public_key' );
		}

		if ( empty( $fs_api_secret_key ) ) {
			$fs_api_secret_key = IBX_WPFomo_Admin::get_settings( 'freemius_secret_key' );
		}

		self::$api = new Freemius_Api( self::$fs_api_scope, $fs_api_store_id, $fs_api_public_key, $fs_api_secret_key, self::$fs_api_sandbox );

		return self::$api;
	}

	/**
	 * connect and test.
	 *
	 * @since 2.0
	 * @return array
	 */
	public static function connect( $fs_api_store_id = '', $fs_api_public_key = '', $fs_api_secret_key = '' ) {
		self::get_api( $fs_api_store_id, $fs_api_public_key, $fs_api_secret_key );

		return self::test();
	}

	/**
	 * test connection.
	 *
	 * @since 2.0
	 * @return array
	 */
	public static function test() {
		$result = array();
		if ( self::get_api()->Test() ) {
			$result = self::get_plugins();
			if ( isset( $result->error ) && ! empty( $result->error->message ) ) {
				return $result;
			}
			update_option( 'ibx_wpfomo_freemius_plugins', $result->plugins );
		}
		return $result;
	}

	/**
	 * Loads includes.
	 *
	 * @since 2.0
	 * @return void
	 */
	public static function load_files() {
		require_once IBX_WPFOMO_FREEMIUS_DIR . 'vendor/freemius/FreemiusBase.php';
		require_once IBX_WPFOMO_FREEMIUS_DIR . 'vendor/freemius/Freemius.php';
	}

	/**
	 * get plugin list.
	 *
	 * @since 2.0
	 * @return array
	 */
	public static function get_plugins() {
		$result = self::get_api()->Api( '/plugins.json' );
		return $result;
	}

	/**
	 * is plugin active.
	 *
	 * @since 2.0
	 * @return array
	 */
	public static function is_plugin_active( $id ) {
		$result = self::get_api()->Api( '/plugins/' . $id . '/is_active.json?is_update=false' );

		return ( is_array( $result ) && isset( $result['is_active'] ) ) ? $result['is_active'] : false;
	}

	/**
	 * filter plugin result.
	 *
	 * @since 2.0
	 * @return array
	 */
	public static function filter_plugins( $plugin_list ) {
		$result = array(
			'plugins' => array(),
		);

		foreach ( $plugin_list['plugins'] as $plugin ) {
			if ( self::is_plugin_active( $plugin->id ) ) {
				$result['plugins'][] = $plugin;
			}
		}

		return $result;
	}

	/**
	 * Get purchases conversions.
	 *
	 * @since 2.0
	 * @param string $plugin_ids
	 * @param string $limit
	 * @return array
	 */
	public static function get_purchases_conversions( $plugin_ids = array(), $limit = '30', $post_id = '' ) {
		$response = array();

		$query = '/purchases.json';

		if ( '' != $limit ) {
			$query .= '?count=' . $limit;
		}

		if ( ! empty( $plugin_ids ) ) {
			$query .= '&plugin_ids=' . implode( ',', $plugin_ids );
		} else {

			$plugins = get_option( 'ibx_wpfomo_freemius_plugins' );

			$ids = array();

			if ( is_array( $plugins ) && ! empty( $plugins ) ) {
				foreach ( $plugins as $plugin ) {
					$ids[] = $plugin->id;
				}
				if ( ! empty( $ids ) ) {
					$query .= '&plugin_ids=' . implode( ',', $ids );
				}
			}
		}

		// get previous saved data for ip_address.
		$ip_data   = array();
		$prev_data = array();

		$ip_data = IBX_WPFomo_Admin::get_post_meta( $post_id, 'conversion_group_ip' );

		$result       = self::get_api()->Api( $query );
		$save_ip_data = array();
		if ( isset( $result->purchases ) ) {
			$purchase_data = json_decode( json_encode( $result->purchases ), true );
			foreach ( $purchase_data as $key => $purchase ) {
				$name = '';

				if ( ! empty( $purchase['user_first_name'] ) && ! empty( $purchase['user_last_name'] ) ) {
					$name = ucfirst( $purchase['user_first_name'] ) . ' ' . ucfirst( $purchase['user_last_name'][0] ) . '.';
				} elseif ( ! empty( $purchase['user_first_name'] ) ) {
					$name = ucfirst( $purchase['user_first_name'] );
				} elseif ( ! empty( $purchase['user_last_name'] ) ) {
					$name = ucfirst( $purchase['user_last_name'] );
				} else {
					$name = IBX_WPFomo_Helper::get_someone_translation();
				}

				$ip_address     = $purchase['payment_ip'];
				$md5_ip_address = md5( $ip_address );

				if ( ! empty( $ip_data ) && array_key_exists( $md5_ip_address, $ip_data ) ) {
					$response[ $key ]['city']    = $ip_data[ $md5_ip_address ]['city'];
					$response[ $key ]['state']   = $ip_data[ $md5_ip_address ]['state'];
					$response[ $key ]['country'] = $ip_data[ $md5_ip_address ]['country'];
				} else {
					$location                       = IBX_WPFomo_Helper::get_location_from_ip( $ip_address );
					$response[ $key ]['city']    = $location['city'];
					$response[ $key ]['state']   = $location['state'];
					$response[ $key ]['country'] = $location['country'];
					// save it for second time use.
					$save_ip_data[ $md5_ip_address ]['city']    = $location['city'];
					$save_ip_data[ $md5_ip_address ]['state']   = $location['state'];
					$save_ip_data[ $md5_ip_address ]['country'] = $location['country'];

				}
				$response[ $key ]['title']        = $purchase['product_title'];
				$response[ $key ]['name']         = $name;
				$response[ $key ]['email']        = $purchase['user_email'];
				$response[ $key ]['plan']         = $purchase['plan_title'];
				$response[ $key ]['url']          = $purchase['product_url'];
				$response[ $key ]['created']      = $purchase['created'];
				$response[ $key ]['time']         = IBX_WPFomo_Helper::get_timeago_html( $purchase['created'] );
				$response[ $key ]['ip_address']   = $ip_address;

				$product_img = IBX_WPFomo_Admin::get_post_meta( $post_id, 'product_img' );

				if ( $product_img ) {
					$response[ $key ]['image']['url'] = $purchase['product_icon'];
				}

			} // End foreach().
			if ( ! empty( $save_ip_data ) ) {
				IBX_WPFomo_Admin::update_post_meta( $post_id, 'conversion_group_ip', $save_ip_data );
			}
		} // End if().

		$response = apply_filters( 'ibx_wpfomo_freemius_get_purchases_conversions', $response );
		return $response;
	}
}
