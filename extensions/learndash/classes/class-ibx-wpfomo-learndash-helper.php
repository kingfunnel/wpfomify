<?php

class IBX_WPFomo_LearnDash_Helper {
	/**
	 * Get course posts.
	 */
	public static function get_courses( $max = '-1' ) {
		 $args = array(
			 'post_type'      => 'sfwd-courses',
			 'post_status'    => 'publish',
			 'posts_per_page' => $max,
			 'orderby'        => 'date',
			 'order'          => 'DESC',
		 );

		 $posts = get_posts( $args );

		 return $posts;
	}

	/**
	 * Get options value for course drop down.
	 */
	public static function get_courses_list( $max = '-1' ) {
		$courses = self::get_courses( $max );
		$options = array(
			'' => __( 'All Courses', 'ibx-wpfomo' ),
		);

		foreach ( $courses as $course ) {
			$options[ $course->ID ] = $course->post_title;
		}

		return $options;
	}

	/**
	 * Get all orders of a course.
	 */
	public static function get_orders_by_course( $course_id, $days = 0, $max = '-1' ) {
		 global $wpdb;

		$table = $wpdb->prefix . 'learndash_user_activity';

		$orders_sql = "
			SELECT * FROM {$table}
			WHERE post_id = %s
			AND activity_type = 'course'
		";

		if ( $days ) {
			$date         = '-' . intval( $days ) . ' days';
			$current_time = strtotime( date( 'Y-m-d h:m:s' ) );
			$old_time     = strtotime( $date );

			$orders_sql .= ' AND activity_started <= %s AND activity_started >= %s';
		}

		$orders_sql .= ' ORDER BY activity_updated DESC';

		if ( '-1' != $max ) {
			$orders_sql .= ' LIMIT ' . intval( $max );
		}

		if ( $days ) {
			$orders = $wpdb->get_results( $wpdb->prepare( $orders_sql, $course_id, $current_time, $old_time ) );
		} else {
			$orders = $wpdb->get_results( $wpdb->prepare( $orders_sql, $course_id ) );
		}

		return $orders;
	}

	/**
	 * Get all orders.
	 */
	public static function get_orders( $days = 0, $max = '-1' ) {
		global $wpdb;

		$table = $wpdb->prefix . 'learndash_user_activity';

		$orders_sql = "
			SELECT * FROM {$table}
			WHERE activity_type = 'course'
		";

		if ( $days ) {
			$date         = '-' . intval( $days ) . ' days';
			$current_time = strtotime( date( 'Y-m-d h:m:s' ) );
			$old_time     = strtotime( $date );

			$orders_sql .= ' AND activity_started <= %s AND activity_started >= %s';
		}

		$orders_sql .= ' ORDER BY activity_updated DESC';

		if ( '-1' != $max ) {
			$orders_sql .= ' LIMIT ' . intval( $max );
		}

		if ( $days ) {
			$orders = $wpdb->get_results( $wpdb->prepare( $orders_sql, $current_time, $old_time ) );
		} else {
			$orders = $wpdb->get_results( $wpdb->prepare( $orders_sql ) );
		}

		return $orders;
	}
}
