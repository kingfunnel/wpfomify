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
class IBX_WPFomo_LLMS_Helper {
	/**
	 * Get orders.
	 */
	static public function get_orders( $days = 0, $max = '-1', $ids = false ) {
		$date = '-' . intval( $days ) . ' days';

		$args = array(
			'post_type'         => 'llms_order',
			'posts_per_page'    => $max,
			'post_status'       => array( 'llms-completed', 'llms-active' ),
			'orderby'			=> 'date',
			'order'				=> 'DESC',
			'date_query'		=> array(
				'after'				=> date( 'Y-m-d', strtotime( $date ) ),
			),
		);

		$orders = get_posts( $args );

		if ( $ids ) {
			$order_ids = array();
			foreach ( $orders as $order ) {
				$order_ids[] = $order->ID;
			}

			return $order_ids;
		}

		return $orders;
	}

	static public function get_order( $order_id ) {
		$order = llms_get_post( $order_id );

		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}

		return $order;
	}

	static public function get_orders_by_course( $course_id, $days = 0, $max = '-1' ) {
		$orders = self::get_orders( $days, $max );
		$course_orders = array();

		foreach ( $orders as $order_post ) {
			$order = llms_get_post( $order_post );

			if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
				continue;
			}

			$order_course_id = $order->get( 'product_id' );

			if ( $course_id == $order_course_id ) {
				$course_orders[] = $order_post;
			}
		}

		return $course_orders;
	}

	/**
	 * Get all downloads from order.
	 */
	static public function get_order_course( $order_post ) {
		$order = llms_get_post( $order_post );

		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return;
		}

		$course[] = array(
			'id'    => $order->get( 'product_id' ),
			'title' => $order->get( 'product_title' ),
		);

		return $course;
	}

	/**
	 * Get user info from order.
	 */
	static public function get_order_customer( $order_post ) {
		$order = llms_get_post( $order_post );

		$user = array(
			'first_name'    => '',
			'last_name'     => '',
			'email'         => '',
			'city'          => '',
			'state'         => '',
			'country'       => '',
		);

		if ( ! $order || ! is_a( $order, 'LLMS_Order' ) ) {
			return $user;
		}

		$user['first_name'] = ucfirst( $order->get( 'billing_first_name' ) );

		$user_lastname = $order->get( 'billing_last_name' );

		if ( ! empty( $user_lastname ) ) {
			$user['last_name'] = ucfirst( $user_lastname[0] ) . '.';
		}

		$user['email']      = $order->get( 'billing_email' );
		$user['city']       = $order->get( 'billing_city' );
		$user['state']      = $order->get( 'billing_state' );
		$user['country']    = $order->get( 'billing_country' );

		$date_format        = 'Y-m-d h:i:s';

		$user['time']       = $order->get_date( 'date', $date_format );

		return $user;
	}

	static public function get_courses( $max = '-1' ) {
		$args = array(
			'post_type'         => 'course',
			'post_status'       => 'publish',
			'posts_per_page'    => $max,
		);

		return get_posts( $args );
	}

	static public function get_membership( $max = '-1' ) {
		$args = array(
			'post_type'         => 'llms_membership',
			'post_status'       => 'publish',
			'posts_per_page'    => $max,
		);

		return get_posts( $args );
	}

	static public function get_courses_list( $max = '-1' ) {
		$courses = self::get_courses( $max );
		$options  = array(
			'' => __( 'All Courses', 'ibx-wpfomo' ),
		);

		foreach ( $courses as $course ) {
			$options[ $course->ID ] = $course->post_title;
		}

		return $options;
	}

	static public function get_display_orders_list() {
		$courses = self::get_courses();
		$membership = self::get_membership();

		$options  = array(
			'' => __( '-- All Courses --', 'ibx-wpfomo' ),
		);
		foreach ( $courses as $course ) {
			$options[ $course->ID ] = $course->post_title . ' [' . __( 'Course', 'ibx-wpfomo' ) . ']';
		}

		$options['_all_membership'] = __( '-- All Membership --', 'ibx-wpfomo' );
		foreach ( $membership as $single_membership ) {
			$options[ $single_membership->ID ] = $single_membership->post_title . ' [' . __( 'Membership', 'ibx-wpfomo' ) . ']';
		}

		return $options;
	}
}
