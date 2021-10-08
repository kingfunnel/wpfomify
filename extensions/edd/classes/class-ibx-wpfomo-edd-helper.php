<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class.
 *
 * @since 1.0.0
 */
class IBX_WPFomo_EDD_Helper {
	/**
	 * Get EDD orders.
	 */
	static public function get_payments( $days = 0, $max = '-1' ) {
		$date 		= '-' . intval( $days ) . ' days';
		$start_date = strtotime( $date );

		$args = array(
			'number'    => $max,
			'status'    => array( 'publish' ),
			'date_query'	=> array(
				'after'			=> date( 'Y-m-d', $start_date ),
			),
		);
		return edd_get_payments( $args );
	}

	/**
	 * Get EDD payments by product.
	 */
	static public function get_payments_by_product( $product_id, $days = 0, $max = '' ) {
		$payments = array();
		$logs = self::get_logs_by_product( $product_id, $days, $max );

		foreach ( $logs as $log ) {
			if ( count( $payments ) == $max ) {
				continue;
			}

			$payment_id = get_post_meta( $log->ID, '_edd_log_payment_id', true );
			$payment = get_post( $payment_id );
			if ( 'publish' == $payment->post_status ) {
				$payments[] = $payment;
			} else {
				continue;
			}
		}

		return $payments;
	}

	/**
	 * Get EDD customer IP by payment_id.
	 */
	static public function get_customer_ip( $payment_id ) {
		return get_post_meta( $payment_id, '_edd_payment_user_ip', true );
	}

	/**
	 * Get EDD logs by product.
	 */
	static public function get_logs_by_product( $product_id, $days = 0, $max ) {
		if ( empty( $max ) ) {
			$max = '-1';
		}

		$date 		= '-' . intval( $days ) . ' days';
		$start_date = strtotime( $date );

		$args = array(
			'post_type'         => 'edd_log',
			'posts_per_page'    => $max,
			'post_status'       => 'publish',
			'post_parent'       => $product_id,
			'tax_query'         => array(
				array(
					'taxonomy'  => 'edd_log_type',
					'field'     => 'slug',
					'terms'     => array( 'sale' ),
				),
			),
			'date_query'	=> array(
				'after'			=> date( 'Y-m-d', $start_date ),
			),
		);

		$logs = get_posts( $args );

		return $logs;
	}

	/**
	 * Get order details.
	 */
	static public function get_payment_details( $payment_id ) {
		$details = edd_get_payment_meta( $payment_id );

		if ( ! $details ) {
			return;
		}

		return $details;
	}

	/**
	 * Get all downloads from order.
	 */
	static public function get_payment_downloads( $payment_id ) {
		$downloads = edd_get_payment_meta_downloads( $payment_id );

		if ( ! $downloads ) {
			return;
		}

		return $downloads;
	}

	/**
	 * Get user info from order.
	 */
	static public function get_payment_user( $payment_id ) {
		$user_info = edd_get_payment_meta_user_info( $payment_id );

		if ( ! $user_info || empty( $user_info ) ) {
			return;
		}

		$user_id        = isset( $user_info['id'] ) ? $user_info['id'] : '';
		$user           = array();
		$user_firstname = '';
		$user_lastname 	= '';

		if ( ! empty( $user_id ) ) {
			$user_firstname = get_user_meta( $user_id, 'first_name', true );
			$user_lastname  = get_user_meta( $user_id, 'last_name', true );
		}

		if ( empty( $user_firstname ) ) {
			$user['name'] = IBX_WPFomo_Helper::get_someone_translation();
		} else {
			$user['name'] = ucfirst( $user_firstname );
			if ( ! empty( $user_lastname ) ) {
				$user['name'] .= ' ' . ucfirst( $user_lastname[0] ) . '.';
			}
		}

		if ( isset( $user_info['email'] ) ) {
			$user['email'] = $user_info['email'];
		}

		return $user;
	}

	/**
	 * Get date info from order.
	 */
	static public function get_payment_date( $payment_id ) {
		$date = edd_get_payment_completed_date( $payment_id );

		if ( ! $date ) {
			return;
		}

		return $date;
	}

	static public function get_products( $max = '-1' ) {
		$args = array(
			'post_type'         => 'download',
			'post_status'       => 'publish',
			'posts_per_page'    => $max,
		);

		return get_posts( $args );
	}

	static public function get_products_list( $max = '-1' ) {
		$products = self::get_products( $max );
		$options  = array(
			'' => __( 'All Products', 'ibx-wpfomo' ),
		);

		foreach ( $products as $product ) {
			$options[ $product->ID ] = $product->post_title;
		}

		return $options;
	}
}
