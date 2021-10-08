<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'IBX_WPFomo_Conversion' ) ) {
	/**
	 * Custom conversation.
	 *
	 * @since 2.0
	 */
	final class IBX_WPFomo_Conversion {
		/**
		 * Holds the error message.
		 *
		 * @since 2.0
		 * @var string $error
		 */
		public static $error = '';

		/**
		 * Holds table name for converstion to migrate.
		 *
		 * @since 2.0
		 * @var string $table_conversion_data
		 */
		public static $table_conversion_data = '';

		/**
		 * Holds table name for converstion to migrate.
		 *
		 * @since 2.0
		 * @var string $table_conversion_meta
		 */
		public static $table_conversion_meta = '';

		/**
		 * Primary class constructor.
		 *
		 * @since 2.0
		 * @return void
		 */
		public static function init() {
			global $wpdb;
			self::$table_conversion_data = $wpdb->prefix . 'ibx_wpfomo_conversion_data';
			self::$table_conversion_meta = $wpdb->prefix . 'ibx_wpfomo_conversion_meta';
			// add_filter( 'mbt_metabox_field_id', __CLASS__ . '::update_custom_conversions', 10, 2);
			// add_filter('mbt_metabox_field_value', __CLASS__ . '::render_custom_conversions', 10, 2);
			add_action( 'ibx_wpfomo_save_post', __CLASS__ . '::save_conversion_post_save' );

		}

		/**
		 * Data migration.
		 *
		 * @since 2.0
		 *
		 * @param array $conversions
		 *
		 * @return void
		 */
		public static function save_conversion_data( $conversions ) {
			global $wpdb;
			$db_table = self::$table_conversion_data;

			if ( count( $conversions ) ) {
				// first check whether the same entry exists in the DB or not.
				$email = $conversions['email'];
				$src = $conversions['src'];
				$query = $wpdb->query( "SELECT * FROM {$db_table} WHERE email='$email' AND src='$src'" );

				if ( ! is_wp_error( $query ) && ! empty( $query ) ) {
					return;
				}


				$i = $wpdb->insert(
					self::$table_conversion_data,
					$conversions
				);

				if ( ! empty( $i ) ) {
					$lastid = $wpdb->insert_id;
				}

				// add logs & details to meta tables.
				$meta_query        = 'INSERT INTO ' . self::$table_conversion_meta . ' (email, meta_key, meta_value) VALUES ';
				$meta_query_values = '';

				foreach ( $conversions as $key => $value ) {
					if ( 'email' !== $key ) {
						if ( ! empty( $meta_query_values ) ) {
							$meta_query_values .= ',';
						}
						if ( 'name' === $key && empty( trim( $value ) ) ) {
							$value = __( 'Someone', 'ibx-wpfomo' );
						}
						$meta_query_values .= " ( '" . $conversions['email'] . "' ,'" . $key . "', '" . $value . "') ";
					}
				}

				$wpdb->query( $meta_query . $meta_query_values );
			}
		}

		/**
		 * Get conversion group.
		 *
		 * @since 2.0
		 *
		 * @param int    $conversion_post_id
		 * @param string $where_condition
		 * @param string $limit_condition
		 *
		 * @return array
		 */
		public static function get_conversion_data( $conversion_post_id = 0, $where_condition = '', $order_by = '', $limit_condition = '', $is_listing = false ) {
			global $wpdb;
			$return_data = array();
			if ( $conversion_post_id > 0 ) {
				if ( '' == $order_by ) {
					$order_by = " ORDER BY time DESC";
				}
				$query = "SELECT id, post_id, title, name, email, city, state, country, image_url, url, time, ip_address FROM {$wpdb->prefix}ibx_wpfomo_conversion_data WHERE post_id={$conversion_post_id}{$where_condition}{$order_by}{$limit_condition}";

				$result = $wpdb->get_results( $query, ARRAY_A );

				if ( count( $result ) ) {
					foreach ( $result as $k => $res ) {
						$time                              = ( $is_listing ) ? $res['time'] : IBX_WPFomo_Helper::get_timeago_html( $res['time'] );
						$return_data[ $k ]['id']           = $res['id'];
						$return_data[ $k ]['post_id']      = $res['post_id'];
						$return_data[ $k ]['title']        = $res['title'];
						$return_data[ $k ]['name']         = $res['name'];
						$return_data[ $k ]['email']        = $res['email'];
						$return_data[ $k ]['city']         = $res['city'];
						$return_data[ $k ]['state']        = $res['state'];
						$return_data[ $k ]['country']      = $res['country'];
						$return_data[ $k ]['image']['url'] = $res['image_url'];
						$return_data[ $k ]['url']          = $res['url'];
						$return_data[ $k ]['time']         = $time;
						$return_data[ $k ]['ip_address']   = $res['ip_address'];
					}
				}
			}
			return $return_data;
		}

		/**
		 * Update conversion group for list table.
		 *
		 * @since 2.0
		 *
		 * @param int   $conversion_id
		 * @param array $data
		 *
		 * @return array
		 */
		public static function update_conversion_data( $conversion_id, $data ) {
			global $wpdb;
			$return_data = false;
			if ( absint( $conversion_id ) > 0 && ! empty( $data ) ) {
				$id      = $conversion_id;
				$updated = $wpdb->update(
					self::$table_conversion_data,
					$data,
					array(
						'id' => $id,
					)
				);

				if ( false === $updated ) {
					// There is an error.
				} else {
					$return_data = true;
				}
			}
			return $return_data;
		}

		/**
		 * Delete conversion group for list table.
		 *
		 * @since 2.0
		 *
		 * @param int $conversion_id
		 *
		 * @return array
		 */
		public static function delete_conversion( $conversion_id ) {
			global $wpdb;
			$return_data = false;
			if ( isset( $conversion_id ) && ! empty( $conversion_id ) ) {
				$id      = $conversion_id;
				$deleted = $wpdb->delete(
					self::$table_conversion_data,
					array(
						'id' => $id,
					)
				);
				if ( false === $deleted ) {
					// There is an error.
				} else {
					$return_data = true;
				}
			}
			return $return_data;
		}

		/**
		 * Get client IP address.
		 *
		 * @since 2.0
		 * @return string
		 */
		public static function get_client_ip() {
			return IBX_WPFomo_Helper::get_client_ip();
		}

		/**
		 * Get client location from IP address.
		 *
		 * @since 2.0
		 * @param string $ip_address
		 * @return array
		 */
		public static function get_location_from_ip( $ip_address ) {
			return IBX_WPFomo_Helper::get_location_from_ip( $ip_address );
		}

		/**
		 * Save custom conversations.
		 *
		 * @since 2.0
		 *
		 * @param string $field_id
		 * @param array  $value
		 *
		 * @return string
		 */
		public static function update_custom_conversions( $field_id, $value ) {
			if ( 'ibx_wpfomo_conversion_group' === $field_id ) {
				global $post;
				$ip = self::get_client_ip();

				foreach ( $value as $conversion_data ) {
					if ( ! empty( $conversion_data['email'] ) ) {
						$conversions = array(
							'post_id'    => $post->ID,
							'name'       => $conversion_data['name'],
							'email'      => $conversion_data['email'],
							'city'       => $conversion_data['city'],
							'state'      => $conversion_data['state'],
							'image_url'  => $conversion_data['image']['url'],
							'url'        => $conversion_data['url'],
							'title'      => $conversion_data['title'],
							'country'    => $conversion_data['country'],
							'time'       => current_time( 'mysql' ),
							'src'        => 'custom',
							'ip_address' => $ip,
						);
					}

					self::save_conversion_data( $conversions );
				}
				return false;
			}

			return $field_id;
		}

		/**
		 * Render custom conversations.
		 *
		 * @since 2.0
		 *
		 * @param array $value
		 * @param array $field
		 *
		 * @return string
		 */
		public static function render_custom_conversions( $value, $field ) {
			if ( 'group' === $field['type'] ) {
				// for conversion.
			}

			return $value;
		}

		/**
		 * Save CSV data.
		 *
		 * @since 2.0
		 *
		 * @return string
		 */
		public static function save_conversion_post_save() {
			global $post;

			$conversion_source = IBX_WPFomo_Admin::get_post_meta( $post->ID, 'conversions_source' );

			if ( 'import_csv' !== $conversion_source ) {
				return;
			}

			if ( ! empty( $post ) ) {
				$csv_value = get_post_meta( $post->ID, 'ibx_wpfomo_csv_file_data', true );
				$index     = array();
				if ( ! empty( $csv_value ) && is_array( $csv_value ) ) {
					unset( $csv_value[0] );// remove header column.
					$name_index    = get_post_meta( $post->ID, 'ibx_wpfomo_csv_name_field', true );
					$email_index   = get_post_meta( $post->ID, 'ibx_wpfomo_csv_email_field', true );
					$title_index   = get_post_meta( $post->ID, 'ibx_wpfomo_csv_title_field', true );
					$time_index    = get_post_meta( $post->ID, 'ibx_wpfomo_csv_time_field', true );
					$country_index = get_post_meta( $post->ID, 'ibx_wpfomo_csv_country_field', true );
					$state_index   = get_post_meta( $post->ID, 'ibx_wpfomo_csv_state_field', true );
					$city_index    = get_post_meta( $post->ID, 'ibx_wpfomo_csv_city_field', true );
					$image_index   = get_post_meta( $post->ID, 'ibx_wpfomo_csv_image_field', true );
					$url_index     = get_post_meta( $post->ID, 'ibx_wpfomo_csv_url_field', true );

					foreach ( $csv_value as $conversion_data ) {

						if ( ! empty( $conversion_data[ $email_index ] ) && filter_var( $conversion_data[ $email_index ], FILTER_VALIDATE_EMAIL ) ) {
								$conversions = array(
									'post_id'   => $post->ID,
									'name'      => isset( $conversion_data[ $name_index ] ) ? $conversion_data[ $name_index ] : '',
									'email'     => $conversion_data[ $email_index ],
									'city'      => isset( $conversion_data[ $city_index ] ) ? $conversion_data[ $city_index ] : '',
									'state'     => isset( $conversion_data[ $state_index ] ) ? $conversion_data[ $state_index ] : '',
									'image_url' => isset( $conversion_data[ $image_index ] ) ? $conversion_data[ $image_index ] : '',
									'url'       => isset( $conversion_data[ $url_index ] ) ? $conversion_data[ $url_index ] : '',
									'title'     => isset( $conversion_data[ $title_index ] ) ? $conversion_data[ $title_index ] : '',
									'country'   => isset( $conversion_data[ $country_index ] ) ? $conversion_data[ $country_index ] : '',
									'time'      => isset( $conversion_data[ $time_index ] ) ? date_format( date_create( $conversion_data[ $time_index ] ), 'Y-m-d H:i:s' ) : current_time( 'mysql' ),
									'src'       => 'csv',
								);

								self::save_conversion_data( $conversions );
						}
					}
					update_post_meta( $post->ID, 'ibx_wpfomo_csv_file_data', array() );
				}
			} // End if().

			return true;
		}

	}
	IBX_WPFomo_Conversion::init();
} // End if().
