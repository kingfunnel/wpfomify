<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class for Envato add-on.
 */
if ( ! class_exists( 'IBX_WPFomo_Envato_Helper' ) ) {

	class IBX_WPFomo_Envato_Helper {
		/**
		 * Envato author's personal token.
		 *
		 * @var       string
		 * @access    private
		 * @since     2.1
		 */
		private static $personal_token;

		/**
		 * Initializes the class.
		 *
		 * @since     2.1
		 */
		public static function init() {
			self::$personal_token = IBX_WPFomo_Admin::get_settings( 'envato_personal_token' );
		}

		/**
		 * Retreive Envato author's username.
		 *
		 * @since     2.1
		 * @return string
		 */
		public static function get_username() {
			$response = self::get_api_response( 'v1/market/private/user/username.json' );

			if ( $response['error'] ) {
				return $response;
			}

			$username = $response['data']['username'];

			update_option( 'ibx_wpfomo_envato_username', $username );

			return $username;
		}

		/**
		 * Retreive Envato author's items.
		 *
		 * @since     2.1
		 * @return array
		 */
		public static function get_items() {
			$username = self::get_username();
			$response = self::get_api_response( 'v1/discovery/search/search/item?username=' . $username );
			$output = array();

			if ( $response['error'] ) {
				return $response;
			}

			$data = $response['data'];

			if ( empty( $data ) ) {
				$output['error'] = __( 'No items found.', 'ibx-wpfomo' );
			}

			if ( ! isset( $data['matches'] ) || empty( $data['matches'] ) ) {
				$output['error'] = __( 'No matching items found.', 'ibx-wpfomo' );
			}

			if ( isset( $output['error'] ) && $output['error'] ) {
				return $output;
			}

			foreach ( $data['matches'] as $item ) {
				$id = $item['id'];
				$output[ $id ] = $item['name'];
			}

			return $output;
		}

		/**
		 * Retreive Envato author's items sales.
		 *
		 * @since     2.1
		 * @return array
		 */
		public static function get_items_sales() {
			$response = self::get_api_response( 'v3/market/author/sales' );
			$items = array();

			if ( $response['error'] ) {
				return $response;
			}

			$data = $response['data'];

			foreach ( $data as $product ) {
				$item = array();
				$item['id'] = '';

				if ( ! isset( $product['item'] ) ) {
					continue;
				}

				if ( isset( $product['item']['id'] ) ) {
					$item['id'] = $product['item']['id'];
				}
				if ( isset( $product['item']['name'] ) ) {
					$item['title'] = $product['item']['name'];
				}
				if ( isset( $product['item']['url'] ) ) {
					$item['url'] = $product['item']['url'];
				}
				if ( isset( $product['item']['previews'] ) ) {
					if ( isset( $product['item']['previews']['icon_preview'] ) ) {
						if ( isset( $product['item']['previews']['icon_preview']['icon_url'] ) ) {
							$item['image']['url'] = $product['item']['previews']['icon_preview']['icon_url'];
						}
					}
				}

				if ( isset( $product['sold_at'] ) ) {
					$item['time'] = $product['sold_at'];
				}
				if ( isset( $product['rating'] ) ) {
					$item['rating'] = $product['rating'];
				}
				if ( isset( $product['rating_count'] ) ) {
					$item['rating_count'] = $product['rating_count'];
				}

				$items[] = $item;
			}

			return $items;
		}

		/**
		 * Retreive the API response.
		 *
		 * @since     2.1
		 * @param string An API URL fragement.
		 * @access private
		 * @return array
		 */
		private static function get_api_response( $fragement ) {
			$url = 'https://api.envato.com/' . $fragement;
			$output = array(
				'error'	=> false,
				'data'	=> ''
			);

			$response = wp_remote_get(
				$url,
				array(
					'headers'     => array(
						'Authorization' => 'Bearer ' . self::$personal_token,
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				$output['error'] = $response->get_error_message();
			} else {
				$output['error'] = false;
				$output['data'] = json_decode( wp_remote_retrieve_body( $response ), 1 );
			}

			return $output;
		}

		/**
		 * Helper logic to connect API.
		 *
		 * @since     2.1
		 * @param string
		 * @return array
		 */
		public static function connect( $personal_token = '' ) {
			if ( ! empty( $personal_token ) ) {
				self::$personal_token = $personal_token;
			}

			$items = self::get_items();

			if ( isset( $items['error'] ) && $items['error'] ) {
				return $items;
			}

			update_option( 'ibx_wpfomo_envato_items', maybe_serialize( $items ) );
			return $items;
		}

	}

	IBX_WPFomo_Envato_Helper::init();
}
