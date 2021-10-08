<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles logic for AJAX operations.
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'IBX_WPFomo_Ajax' ) ) {

	class IBX_WPFomo_Ajax {
		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __construct() {
			add_action( 'wp_ajax_nopriv_ibx_wpfomo_get_conversions', array( $this, 'get_conversions' ) );
			add_action( 'wp_ajax_ibx_wpfomo_get_conversions', array( $this, 'get_conversions' ) );
			add_action( 'wp_ajax_ibx_wpfomo_toggle_status', array( $this, 'toggle_status' ) );
			add_action( 'wp_ajax_ibx_wpfomo_parse_custom_form_from_url', array( $this, 'parse_custom_form_from_url' ) );
			add_action( 'wp_ajax_ibx_wpfomo_save_custom_form_data', array( $this, 'save_custom_form_data' ) );
			add_action( 'wp_ajax_nopriv_ibx_wpfomo_save_custom_form_data', array( $this, 'save_custom_form_data' ) );
			add_action( 'wp_ajax_ibx_wpfomo_upload_file', array( $this, 'upload_file' ) );
		}

		/**
		 * AJAX headers.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function ajax_headers() {
			if ( ! headers_sent() ) {
				send_origin_headers();
				send_nosniff_header();
				header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
				header( 'X-Robots-Tag: noindex' );
				nocache_headers();
				status_header( 200 );
			}
		}

		/**
		 * Get conversions.
		 *
		 * @since 1.0.0
		 */
		public function get_conversions() {
			self::ajax_headers();

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ibx_fomo_conversion_nonce_front' ) ) {
				return;
			}
			if ( ! isset( $_POST['ids'] ) || empty( $_POST['ids'] ) || ! is_array( $_POST['ids'] ) ) {
				return;
			}

			// get cache duration from settings.
			$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
			if ( ! $cache_duration || empty( $cache_duration ) ) {
				$cache_duration = 45;
			}

			$ids  = array_map( 'absint', $_POST['ids'] );
			$data = array();

			foreach ( $ids as $id ) {
				$settings       = MetaBox_Tabs::get_metabox_settings( $id );
				$data['config'] = array(
					'id'               => $id,
					'initial_delay'    => ! empty( $settings->initial_delay ) ? $settings->initial_delay * 1000 : 0,
					'display_duration' => ! empty( $settings->display_time ) ? $settings->display_time * 1000 : 0,
					'delay_each'       => ! empty( $settings->delay_between ) ? $settings->delay_between * 1000 : 0,
					'loop'             => absint( $settings->loop ),
					'randomize'        => isset( $settings->randomize ) ? absint( $settings->randomize ) : 0,
				);

				if ( 'conversion' === $settings->type ) {
					$data['config']['source'] = $settings->conversions_source;
				}

				ob_start();
				include IBX_WPFOMO_DIR . 'includes/frontend-notification.php';
				$content = ob_get_clean();

				$data['content'] = $content;
			}

			echo json_encode( $data );
			die;
		}

		/**
		 * Toggle notification status - active/inactive from post columns.
		 *
		 * @since 1.0.0
		 */
		public function toggle_status() {
			self::ajax_headers();

			$error = false;

			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ibx_wpfomo_toggle_status' ) ) {
				$error = true;
			}

			if ( ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) || ! absint( $_POST['post_id'] ) ) {
				$error = true;
			}

			if ( $error ) {
				_e( 'There is an error updating status.', 'ibx-wpfomo' );
				die();
			}

			$post_id = absint( $_POST['post_id'] );
			$status  = 'active' == sanitize_text_field( $_POST['status'] ) ? '1' : '0';

			update_post_meta( $post_id, 'ibx_wpfomo_active_check', $status );

			echo 'success';
			die();
		}

		/**
		 * Parse form from url.
		 *
		 * @since 1.0.0
		 */
		public function parse_custom_form_from_url() {
			self::ajax_headers();

			$error = false;

			if ( ! isset( $_POST['ibx_wpfomo_custom_form_url'] ) || empty( $_POST['ibx_wpfomo_custom_form_url'] ) ) {
				$error = true;
			}
			if ( $error ) {
				echo __( 'There is an error getting form details.', 'ibx-wpfomo' );
				die();
			}
			$ibx_wpfomo_custom_form_url = esc_url( $_POST['ibx_wpfomo_custom_form_url'] );
			// IBX_WPFomo_Form_Parser::parse_dom( $ibx_wpfomo_custom_form_url );
			echo json_encode( IBX_WPFomo_Form_Parser::parse_forms( $ibx_wpfomo_custom_form_url ) );
			die();
		}

		/**
		 * Parse form from url.
		 *
		 * @since 1.0.0
		 */
		public function save_custom_form_data() {
			self::ajax_headers();

			$custom_form_data = wp_unslash( $_POST['ibx_wpfomo_custom_form_data'] );

			$post_id                = absint( $_POST['post_id'] );
			$form_name              = $custom_form_data['form_key'];
			$form_src_url           = $custom_form_data['form_src_url'];
			$form_src_conversion_id = $custom_form_data['form_src_conversion_id'];
			$form_email_select      = $custom_form_data['custom_form_email_select'];
			$form_name_select       = $custom_form_data['custom_form_name_select'];
			$form_title             = $custom_form_data['custom_form_title'];
			$name                   = '';
			$email 					= '';
			$gravatar_url 			= '';

			// get fields mapping.
			foreach ( $custom_form_data['form_data'] as $key => $value ) {
				if ( ! empty( $form_email_select ) ) {
					if ( $value['field_name'] === $form_email_select ) {
						$email        = trim( $value['field_value'] );
						$email        = strtolower( $email );
						//$gravatar_url = 'https://www.gravatar.com/avatar/' . md5( $email );
					}
				}

				if ( ! empty( $form_name_select ) ) {
					if ( $value['field_name'] === $form_name_select ) {
						$name = $value['field_value'];
					}
				}
			}

			$error = false;

			if ( ! isset( $_POST['ibx_wpfomo_custom_form_data'] ) || empty( $_POST['ibx_wpfomo_custom_form_data'] ) ) {
				$error = true;
			}
			if ( $error ) {
				// echo __( 'There is an error getting form details.', 'ibx-wpfomo' );
				echo 0;
				die();
			}
			$ip       = IBX_WPFomo_Conversion::get_client_ip();
			$location = IBX_WPFomo_Conversion::get_location_from_ip( $ip );

			$conversions = array(
				'post_id'    => $form_src_conversion_id, // post id for conversation.
				'name'       => $name,
				'email'      => $email,
				'city'       => $location['city'],
				'state'      => $location['state'],
				'country'    => $location['country'],
				'image_url'  => '',
				'url'        => $form_src_url,
				'title'      => $form_title,
				'time'       => current_time( 'mysql' ),
				'src'        => $form_name,
				'ip_address' => $ip,
			);

			IBX_WPFomo_Conversion::save_conversion_data( $conversions );
			die();
		}

		/**
		 * Upload File.
		 *
		 * @since 1.0.0
		 */
		public function upload_file() {
			self::ajax_headers();

			$response = array();
			$error = false;

			if ( ! isset( $_POST['action'] ) || empty( $_POST['action'] ) || ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) ) {
				$error = __( 'Invalid data.', 'ibx-wpfomo' );
			} elseif ( ! isset( $_POST['accept'] ) || empty( $_POST['accept'] ) ) {
				$error = __( 'Invalid file format.', 'ibx-wpfomo' );
			} elseif ( ! isset( $_FILES ) || empty( $_FILES ) ) {
				$error = __( 'Invalid or empty file input.', 'ibx-wpfomo' );
			} else {
				$post_id = absint( $_POST['post_id'] );
				$filename = sanitize_file_name( $_FILES['file']['name'] );
				$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
				$file = array();

				if ( ! is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
					$error = __( 'There is an error uploading file.', 'ibx-wpfomo' );
				} else {
					$file = $_FILES;
				}

				if ( empty( $file ) ) {
					$error = __( 'There is an error uploading file.', 'ibx-wpfomo' );
				}

				if ( ! $error ) {
					$response['success'] = true;
					$response['success_msg'] = __( 'File uploaded successfully. Please map the fields below.', 'ibx-wpfomo' );

					if ( ( $handle = fopen( $file['file']['tmp_name'], 'r' ) ) !== false ) { // @codingStandardsIgnoreLine.
						$i = 0;
						$csv = array();

						while ( ( $row = fgetcsv( $handle ) ) !== false ) { // @codingStandardsIgnoreLine.
							// $row is an array of the csv elements.
							if ( 0 == $i ) {
								$response['file_columns'] = $row;
							}
							if ( ! empty( $row ) ) {
								// foreach ( $row as $i => $row_data ) {
								// 	$row[ $i ] = mb_convert_encoding( $row_data, 'UTF-8' );
								// }
								$csv[] = $row;
							}
							$i++;
						}

						update_post_meta( $post_id, 'ibx_wpfomo_csv_file_data', $csv );
						fclose( $handle );
					}
				}
			} // End if().

			if ( $error ) {
				$response['error'] = true;
				$response['error_msg'] = $error;
			}

			if ( file_exists( $_FILES['file']['tmp_name'] ) ) {
				unlink( $_FILES['file']['tmp_name'] );
			}

			echo json_encode( $response );
			die();
		}
	}

	$ibx_wpfomo_ajax = new IBX_WPFomo_Ajax();
} // End if().
