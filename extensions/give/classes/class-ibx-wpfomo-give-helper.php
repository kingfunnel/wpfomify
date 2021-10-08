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
class IBX_WPFomo_Give_Helper {
	/**
	 * Get orders.
	 */
	static public function get_payments( $days = 0, $max = '-1' ) {
		$date 		= '-' . intval( $days ) . ' days';
		$start_date = strtotime( $date );

		$args = array(
			'number'    	=> $max,
			'status'    	=> array( 'publish' ),
			'date_query'	=> array(
				'after'			=> date( 'Y-m-d', $start_date ),
			),
		);

		return give_get_payments( $args );
	}

	static public function get_payments_by_form( $form_id, $days = 0, $max = '' ) {
		$payments = array();
		$logs = self::get_logs_by_donation( $form_id, $days, $max );

		foreach ( $logs as $log ) {
			if ( count( $payments ) == $max ) {
				continue;
			}

			$payment_id = get_post_meta( $log->ID, '_give_log_payment_id', true );
			$payment = get_post( $payment_id );

			if ( 'publish' == $payment->post_status ) {
				$payments[] = $payment;
			} else {
				continue;
			}
		}

		return $payments;
	}

	static public function get_logs_by_form( $form_id, $days = 0, $max ) {
		if ( empty( $max ) ) {
			$max = '-1';
		}

		$date 		= '-' . intval( $days ) . ' days';
		$start_date = strtotime( $date );

		$args = array(
			'post_type'         => 'give_log',
			'posts_per_page'    => $max,
			'post_status'       => 'publish',
			'post_parent'       => $form_id,
			'tax_query'         => array(
				array(
					'taxonomy'  => 'give_log_type',
					'field'     => 'slug',
					'terms'     => array( 'sale' ),
				),
			),
			'date_query'		=> array(
				'after'				=> date( 'Y-m-d', $start_date ),
			),
		);

		$logs = get_posts( $args );

		return $logs;
	}

	/**
	 * Get order details.
	 */
	static public function get_payment_details( $payment_id ) {
		$details = give_get_payment_meta( $payment_id );

		if ( ! $details ) {
			return;
		}

		return $details;
	}

	/**
	 * Get donation form from payment.
	 */
	static public function get_payment_form_id( $payment_id ) {
		$form_id = give_get_payment_form_id( $payment_id );

		if ( ! $form_id ) {
			return;
		}

		return $form_id;
	}

	/**
	 * Get user info from order.
	 */
	static public function get_payment_user( $payment_id ) {
		$user_info = give_get_payment_meta_user_info( $payment_id );

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
		$date = give_get_payment_completed_date( $payment_id );

		if ( ! $date ) {
			return;
		}

		return $date;
	}

	static public function get_forms( $max = '-1' ) {
		$args = array(
			'post_type'         => 'give_forms',
			'post_status'       => 'publish',
			'posts_per_page'    => $max,
		);

		return get_posts( $args );
	}

	static public function get_forms_list( $max = '-1' ) {
		$forms = self::get_forms( $max );
		$options  = array(
			'' => __( 'All Forms', 'ibx-wpfomo' ),
		);

		foreach ( $forms as $form ) {
			$options[ $form->ID ] = $form->post_title;
		}

		return $options;
	}
}
