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
class IBX_WPFomo_WooCommerce_Helper {
	/**
	 * Get orders.
	 */
	public static function get_orders( $days = 0, $max = '-1', $ids = false ) {
		$date = '-' . intval( $days ) . ' days';

		$args = array(
			'post_type'      => 'shop_order',
			'posts_per_page' => $max,
			'post_status'    => array( 'wc-completed', 'wc-processing', 'wc-pending' ),
			'orderby'        => 'date',
			'order'          => 'DESC',
			'date_query'     => array(
				'after' => date( 'Y-m-d', strtotime( $date ) ),
			),
		);

		$orders = get_posts( $args );

		if ( $ids ) {
			$order_ids = array();
			foreach ( $orders as $order ) {
				$order_ids[] = $order->ID;
			}
		}

		return $orders;
	}

	/**
	 * Get all orders given a Product ID.
	 *
	 * @global $wpdb
	 *
	 * @param array $product_id The product ID.
	 *
	 * @return array An array of WC_Order objects.
	 */
	public static function get_orders_by_product( $query_args, $days = 0, $max = '-1' ) {
		global $wpdb;
		$category_product_ids = array();
		$unique_product_ids = array();
		$product_ids = array();
		$str_unique_product_ids = '';

		if ( ! empty( $query_args['woo_product_orders'] ) || ! empty( $query_args['woo_product_orders_category'] ) ) {
			if ( ! empty( $query_args['woo_product_orders_category'] ) ) {
				$category_product_ids = self::get_product_id_by_term_id( $query_args['woo_product_orders_category'] );
			}
			if ( ! empty( $query_args['woo_product_orders'] ) ) {
				$product_ids = $query_args['woo_product_orders'] ;
			}

			$unique_product_ids = array_unique( array_merge( $category_product_ids, $product_ids ) );

			$str_unique_product_ids = implode( ', ', $unique_product_ids );
		}

		$date       = '-' . intval( $days ) . ' days';
		$start_date = date( 'Y-m-d 00:00:00', strtotime( $date ) );
		$end_date   = date("Y-m-d", time() + 86400);//date( 'Y-m-d h:m:s' );

		$orders_sql = "select 
				p.ID as order_id,
				p.post_date,
				i.order_item_name,
				im.meta_value as product_id
			from 
				{$wpdb->prefix}posts as p,				
				{$wpdb->prefix}woocommerce_order_items as i,
				{$wpdb->prefix}woocommerce_order_itemmeta as im
			where 
				p.post_type = 'shop_order'				
				and p.ID = i.order_id
                and i.order_item_id = im.order_item_id
                and im.meta_key = '_product_id'
				and p.post_date BETWEEN '%s' AND '%s'
				and p.post_status IN ('wc-completed', 'wc-processing', 'wc-pending', 'wc-on-hold')				
				";

		if ( '' !== $str_unique_product_ids ) {
			$orders_sql .= " and im.meta_value IN ( $str_unique_product_ids ) ";
		}

		//order by.
		$orders_sql .= ' order by p.ID DESC ';

		if ( '-1' != $max ) {
			$orders_sql .= ' limit ' . intval( $max );
		}

		$order_data = $wpdb->get_results( $wpdb->prepare( $orders_sql, $start_date, $end_date ) );

		$orders    = array();

		if ( null == $order_data || empty( $order_data ) ) {
			return;
		}
		if ( is_array( $order_data ) ) {
			foreach ( $order_data as $order ) {
				if ( ! isset( $orders[ $order->order_id ] ) || ! is_array( $orders[ $order->order_id ] ) ) {
					$orders[ $order->order_id ] = array();
				}
				array_push( $orders[ $order->order_id ], $order->product_id );
			}
		}

		return $orders;
	}

	/**
	 * Get all products from order.
	 */
	public static function get_order_items( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$product_data = array();

		foreach ( $order->get_items() as $item_key => $item_values ) {
			// get_data array keys:
			// 'name', 'product_id', 'variation_id', 'quantity',
			// 'tax_class', 'subtotal', 'subtotal_tax', 'total',
			// 'total_tax'
			$product_data[] = array(
				'id'   => $item_values->get_product_id(),
				'data' => $item_values->get_data(),
			);
		}

		return $product_data;
	}

	/**
	 * Get a product from order.
	 */
	public static function get_order_item( $order_id, $product_id = '' ) {
		// Get the order items.
		$items = self::get_order_items( $order_id );

		if ( ! is_array( $items ) || ! count( $items ) ) {
			return;
		}

		return $items[0];
	}

	/**
	 * Get billing address from order.
	 */
	public static function get_billing_details( $order_id, $key = '' ) {
		// Get the order.
		$order = wc_get_order( $order_id );

		// Get the order data.
		$order_data = $order->get_data();

		if ( empty( $key ) ) {
			return $order_data;
		}
		// $key can be:
		// 'first_name', 'last_name', 'company', 'address_1', 'address_2',
		// 'city', 'state', 'postcode', 'country', 'email', 'phone'
		if ( isset( $order_data[ $key ] ) ) {
			return $order_data[ $key ];
		}
	}

	/**
	 * Get products from database.
	 */
	public static function get_products( $max = '-1' ) {
		global $wpdb;

		$type = 'product';

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"
            SELECT * FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_status = 'publish'
            ORDER BY post_date DESC
        ",
				$type
			)
		);

		return $posts;
	}

	/**
	 * Build options list for select field.
	 */
	public static function get_products_list( $max = '-1' ) {
		global $wpdb;

		$type = 'product';

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"
            SELECT ID, post_title FROM {$wpdb->posts}
            WHERE post_type = %s
            AND post_status = 'publish'
            ORDER BY post_date DESC
        ",
				$type
			)
		);

		$options = array(
			'' => __( 'All Products', 'ibx-wpfomo' ),
		);

		foreach ( $posts as $post ) {
			$options[ $post->ID ] = $post->post_title;
		}

		return $options;
	}

	public static function get_timeago_html( $time = false ) {
		if ( ! $time ) {
			return;
		}

		$time_ago = IBX_WPfomo_Helper::get_timeago_html( $time );
		if ( $time_ago ) {
			return $time_ago;
		}

		$offset     = get_option( 'gmt_offset' ) * 60 * 60; // Time offset in seconds
		$timestamp  = strtotime( $time );
		$local_time = $timestamp + $offset;

		$time = human_time_diff( $local_time, current_time( 'timestamp' ) );
		$ago 	= IBX_WPFomo_Helper::get_ago_translation();

		ob_start(); ?>
		<?php echo  esc_html( $time ) . ' ' . $ago; ?>
		<?php
		$time_ago = ob_get_clean();

		return $time_ago;
	}

	public static function get_product_reviews( $args = null ) {
		$reviews = array();
		if ( empty( $args ) || ! isset( $args ) ) {
			$args = array(
				'number'      => 20,
				'status'      => 'approve',
				'post_status' => 'publish',
				'post_type'   => 'product',
				'orderby'     => 'comment_date',
				'order'       => 'DESC',
			);
		}

		$comments = get_comments( $args );
		foreach ( $comments as $key => $comment ) :
			$product_title = get_the_title( $comment->comment_post_ID );

			// $product = wc_get_product( $comment->comment_post_ID );
			// $product_title = $product->get_name();

			$product_url = get_permalink( $comment->comment_post_ID );
			$star_rating = get_comment_meta( $comment->comment_ID, 'rating', true );
			$image       = wp_get_attachment_image_src( get_post_thumbnail_id( $comment->comment_post_ID ), 'shop_thumbnail' );

			$reviews[ $key ]['name']         = $comment->comment_author;
			$reviews[ $key ]['email']        = $comment->comment_author_email;
			$reviews[ $key ]['title']        = $product_title;
			$reviews[ $key ]['time']         = $comment->comment_date_gmt;
			$reviews[ $key ]['product_id']   = $comment->comment_post_ID;
			$reviews[ $key ]['url']          = $product_url;
			$reviews[ $key ]['rating']       = $star_rating;
			$reviews[ $key ]['image']['url'] = $image[0];

		endforeach;

		return $reviews;
	}

	public static function get_product_id_by_term_id( $term_id = null ) {
		$product_ids = array();

		if ( ! empty( $term_id ) ) {
			$args = array(
				'post_type'             => 'product',
				'post_status'           => 'publish',
				'fields'                => 'ids',
				'posts_per_page'		=> -1,
				'tax_query'				=> array(
					array(
						'taxonomy'      => 'product_cat',
						'field'    		=> 'term_id',
						'terms'         => $term_id,
						'operator'      => 'IN',
					),
				),
			);

			$product_ids = get_posts( $args );
		}

		return $product_ids;
	}
}
