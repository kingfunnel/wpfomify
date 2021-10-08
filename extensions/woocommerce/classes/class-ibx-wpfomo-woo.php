<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_WooCommerce {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Primary class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init_hooks' ) );
		add_filter( 'ibx_wpfomo_update_meta_once', array( $this, 'update_meta' ), 10, 1 );
		add_action( 'ibx_wpfomo_save_post', array( $this, 'clear_transients' ) );
		add_filter( 'mbt_filter_suggest_field_data', array( $this, 'category_field_action' ), 10, 2 );
	}

	/**
	 * Initialize the hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		require_once IBX_WPFOMO_WOOCOMMERCE_DIR . 'classes/class-ibx-wpfomo-woo-helper.php';

		add_action( 'ibx_wpfomo_before_metabox_load', array( $this, 'init_fields' ) );
		add_action( 'ibx_wpfomo_admin_meta_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'ibx_wpfomo_conversion_data', array( $this, 'add_conversion_data' ), 10, 2 );
		add_filter( 'ibx_wpfomo_reviews_data', array( $this, 'add_reviews_data' ), 10, 2 );
		add_filter( 'ibx_wpfomo_conversion_analytics_source', array( $this, 'add_conversion_analytics_source' ) );
		add_filter( 'ibx_wpfomo_conversion_count', array( $this, 'add_conversion_count' ), 10, 2 );
	}

	/**
	 * Clears transients on post update.
	 *
	 * @since 1.0.1
	 */
	public function clear_transients( $post_id ) {
		if ( isset( $_POST['ibx_wpfomo_conversions_source'] )
			&& 'woocommerce' == sanitize_text_field( $_POST['ibx_wpfomo_conversions_source'] ) ) {
			delete_transient( 'ibx_wpfomo_woocommerce_orders_' . $post_id );
			delete_transient( 'ibx_wpfomo_woocommerce_reviews_' . $post_id );

			// cache data.
			$this->get_data( $post_id );
		}
	}

	/**
	 * Updates the meta for the new fields introduced in WPfomify 2.0
	 *
	 * @since 1.0.2
	 */
	public function update_meta( $data ) {
		$data['woo'] = array(
			'condition' => array(
				'ibx_wpfomo_type'               => 'conversion',
				'ibx_wpfomo_conversions_source' => 'woocommerce',
			),
			'fields'    => array(
				'ibx_wpfomo_woo_product_img' => array( 'ibx_wpfomo_product_img' ),
				'ibx_wpfomo_woo_orders'      => array( 'ibx_wpfomo_display_last' ),
			),
		);

		return $data;
	}

	/**
	 * Init fields for WooCommerce integration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_fields() {
		add_filter( 'ibx_wpfomo_field_fomo_type', array( $this, 'hide_fields' ), 10, 1 );
		add_filter( 'ibx_wpfomo_field_conversions_source', array( $this, 'conversion_source' ), 10, 1 );
		add_filter( 'ibx_wpfomo_field_reviews_source', array( $this, 'reviews_source' ), 10, 1 );
		add_filter( 'ibx_wpfomo_metabox_fields', array( $this, 'add_fields' ), 10, 1 );
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-woocommerce-meta', IBX_WPFOMO_WOOCOMMERCE_URL . 'assets/js/meta.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
	}

	/**
	 * Register data for category suggest field.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function category_field_action( $data, $action ) {
		if ( 'get_product_categories' != $action ) {
			return $data;
		}

		$term_query = new WP_Term_Query( array(
			'taxonomy' => 'product_cat',
			'orderby' => 'name',
			'order' => 'ASC',
			'hide_empty' => false,
		) );

		$terms = $term_query->get_terms();

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$data[ $term->term_id ] = $term->name;
			}
		}

		return $data;
	}

	/**
	 * Conversion fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_fields() {
		$fields = array(
			'woo_template'        => array(
				'type'      => 'template',
				'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
				'default'   => array(
					'0' => __( '{{name}} purchased', 'ibx-wpfomo' ),
					'1' => '{{title}}',
					'2' => '{{time}}',
				),
				'variables' => array( '{{name}}', '{{city}}', '{{state}}', '{{country}}', '{{title}}', '{{time}}' ),
				'sanitize'  => false,
			),
			'woo_product_orders'  => array(
				'type'        => 'suggest',
				'label'   => __( 'Show purchase of', 'ibx-wpfomo' ),
				'placeholder' => __( 'Choose Products...', 'ibx-wpfomo' ),
				'action'      => 'get_posts',
				'render'		=> IBX_WPFomo_Helper::is_pro_version(),
				'options'     => array(
					'post_type' => 'product',
				),
			),
			'woo_product_orders_category'  => array(
				'type'        => 'suggest',
				'label'   => __( 'Show purchase of product categories', 'ibx-wpfomo' ),
				'placeholder' => __( 'Choose Categories...', 'ibx-wpfomo' ),
				'action'      => 'get_product_categories',
				'render'	=> IBX_WPFomo_Helper::is_pro_version(),
			),
			'woo_product_link'    => array(
				'type'    => 'select',
				'label'   => __( 'Link Notification to', 'ibx-wpfomo' ),
				'default' => 'none',
				'options' => array(
					'none'    => __( 'None', 'ibx-wpfomo' ),
					'product' => __( 'Product Page', 'ibx-wpfomo' ),
					'custom'  => __( 'Custom URL', 'ibx-wpfomo' ),
				),
				'help'    => __( 'You can link notification with product page or custom URL.', 'ibx-wpfomo' ),
				'toggle'  => array(
					'custom' => array(
						'fields' => array( 'woo_custom_url' ),
					),
				),
			),
			'woo_custom_url'      => array(
				'type'    => 'text',
				'label'   => __( 'Custom URL', 'ibx-wpfomo' ),
				'default' => '',
			),
			'woo_review_template' => array(
				'type'      => 'template',
				'label'     => __( 'Review Template', 'ibx-wpfomo' ),
				'default'   => array(
					'0' => '{{rating}} by {{name}}',
					'1' => '{{title}}',
					'2' => '{{time}}',
				),
				'variables' => array( '{{rating}}', '{{title}}', '{{name}}' ),
				'sanitize'  => false,
				'priority'  => 100,
			),
			'woo_products'        => array(
				'type'        => 'suggest',
				'label'       => __( 'Products', 'ibx-wpfomo' ),
				'placeholder' => __( 'All Products Else Choose Products...', 'ibx-wpfomo' ),
				'action'      => 'get_posts',
				'options'     => array(
					'post_type' => 'product',
				),
				'priority'    => 150,
			),
		);

		return $fields;
	}

	/**
	 * Hide fields when reviews is selected.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function hide_fields( $data ) {
		$fields = $this->get_fields();

		// Hide fields from other field types.
		foreach ( $data['options'] as $option_key => $option_val ) {
			if ( 'conversion' !== $option_key ) {
				foreach ( $fields as $key => $field ) {
					$data['hide'][ $option_key ]['fields'][] = $key;
				}
			}
		}

		return $data;
	}

	/**
	 * Add WooCommerce as conversion source.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function conversion_source( $data ) {
		$data['options']['woocommerce'] = 'WooCommerce';

		foreach ( $this->get_fields() as $field_key => $field_data ) {
			if ( 'woo_review_template' == $field_key ) {
				continue;
			}

			$data['toggle']['woocommerce']['fields'][] = $field_key;
		}

		$data['toggle']['woocommerce']['fields'][] = 'product_img';
		$data['hide']['custom']['fields']          = array( 'woo_custom_url', 'woo_review_template' );
		$data['hide']['import_csv']['fields'][]    = 'woo_custom_url';
		$data['hide']['custom_form_url']['fields'][]   = 'woo_custom_url';
		$data['hide']['freemius']['fields'][]      = 'woo_custom_url';
		$data['hide']['woocommerce']['fields']	   = array( 'woo_review_template', 'woo_products' );

		return $data;
	}

	/**
	 * Add WooCommerce as reviews source.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function reviews_source( $data ) {
		$data['options']['woocommerce'] = 'WooCommerce';

		foreach ( $this->get_fields() as $field_key => $field_data ) {
			if ( 'woo_template' == $field_key ) {
				continue;
			}

			$data['toggle']['woocommerce']['fields'][] = $field_key;
		}

		$data['toggle']['woocommerce']['fields'][] = 'product_img';
		$data['hide']['custom']['fields'][]        = 'woo_custom_url';
		$data['hide']['woocommerce']['fields']     = array( 'review_template', 'woo_custom_url', 'woo_product_orders' );

		return $data;
	}

	/**
	 * Add fields for WooCommerce conversion.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function add_fields( $data ) {
		$fields = $this->get_fields();

		foreach ( $fields as $key => $field ) {
			$data['content']['sections']['content_section']['fields'][ $key ] = $field;
		}

		return $data;
	}

	/**
	 * Add conversion content for WooCommerce.
	 *
	 * @since 1.0.0
	 * @param array  $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( 'woocommerce' != $settings->conversions_source ) {
			return $data;
		}

		$orders = $this->get_data( $settings->post_id, $settings );

		if ( empty( $orders ) ) {
			return $data;
		}

		//rsort( $orders );

		foreach ( $orders as $order_id => $products ) {
			// Get the billing details.
			$order_data = IBX_WPFomo_WooCommerce_Helper::get_billing_details( $order_id );
			$billing    = $order_data['billing'];
			$time       = IBX_WPFomo_WooCommerce_Helper::get_timeago_html( $order_data['date_created'] );

			$billing_name = $billing['first_name'];

			if ( empty( $billing_name ) ) {
				$billing_name = IBX_WPFomo_Helper::get_someone_translation();
			} else {
				if ( isset( $billing['last_name'] ) && ! empty( $billing['last_name'] ) ) {
					$billing_name .= ' ' . $billing['last_name'][0] . '.';
				}
			}

			foreach ( $products as $product_id ) {
				$product_name = get_the_title( $product_id );

				if ( ! $product_id || empty( $product_id ) ) {
					continue;
				}

				// Data to render notification.
				$fields_data = array(
					'title'   => $product_name,
					'name'    => $billing_name,
					'email'   => $billing['email'],
					'city'    => $billing['city'],
					'state'   => $billing['state'],
					'country' => $billing['country'],
					'ip_address' => $order_data['customer_ip_address'],
				);

				$fields_data['time'] = $time;

				// Product URL or Custom URL.
				if ( 'product' == $settings->woo_product_link ) {
					$fields_data['url'] = get_permalink( $product_id );
				}
				if ( 'custom' == $settings->woo_product_link ) {
					$fields_data['url'] = esc_url( $settings->woo_custom_url );
				}

				// Product image.
				if ( isset( $settings->product_img ) && $settings->product_img ) {
					$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'shop_thumbnail' );

					if ( ! empty( $product_image ) ) {
						$fields_data['image'] = array(
							'url' => $product_image[0],
						);
					}
				}

				$data['fields'][] = $fields_data;
			} // End foreach().
		} // End foreach().

		$data['template'] = $settings->woo_template;

		return $data;
	}

	/**
	 * Add reviews content for WooCommerce.
	 *
	 * @since 1.0.0
	 * @param array  $data
	 * @param object $settings
	 * @return array
	 */
	public function add_reviews_data( $data, $settings ) {
		if ( 'woocommerce' != $settings->reviews_source ) {
			return $data;
		}

		$transient_key = 'ibx_wpfomo_woocommerce_reviews_' . $settings->post_id;

		// get cache duration from settings.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		$reviews = get_transient( $transient_key );

		if ( ! $reviews || empty( $reviews ) ) {
			$args = array(
				'number'      => $settings->display_last,
				'status'      => 'approve',
				'post_status' => 'publish',
				'post_type'   => 'product',
				'orderby'     => 'comment_date',
				'order'       => 'DESC',
			);
			if ( ! empty( $settings->display_last ) ) {
				$after              = $settings->display_last_days . ' day ago';
				$args['date_query'] = array(
					'after'     => $after,
					'before'    => 'tomorrow',
					'inclusive' => true,
				);
			}
			if ( ! empty( $settings->woo_products ) ) {
				$products         = $settings->woo_products;
				$args['post__in'] = $products;
			}

			$reviews = IBX_WPFomo_WooCommerce_Helper::get_product_reviews( $args );

			if ( $reviews && ! empty( $reviews ) ) {
				// Store data in transient.
				set_transient( $transient_key, $reviews, ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
			} else {
				return $data;
			}
		}

		rsort( $reviews );
		foreach ( $reviews as $fields_data ) {

			if ( 'custom' === $settings->woo_product_link && '' !== $settings->woo_custom_url ) {
				$review['url'] = $settings->woo_custom_url;
			}

			// Data to render notification.
			$fields_data['time'] = IBX_WPFomo_WooCommerce_Helper::get_timeago_html( $fields_data['time'] );

			$data['fields'][] = $fields_data;
		}

		$data['template'] = $settings->woo_review_template;

		return $data;
	}

	protected function get_data( $post_id, $settings = null ) {
		$settings = empty( $settings ) ? MetaBox_Tabs::get_metabox_settings( $post_id ) : $settings;
		$transient_key = 'ibx_wpfomo_woocommerce_orders_' . $post_id;

		// get cache duration from settings.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		$orders = get_transient( $transient_key );
		//check if multidimentional as per new format if not then query instead of using transient.
		if ( ! empty( $orders ) ) {
			$tmp = array_filter( $orders, 'is_array' );
			if ( count( $tmp ) == 0 ) {
				$orders = array();
			}
		}
		if ( ! $orders || empty( $orders ) ) {
			if ( isset( $settings->woo_product_orders ) || isset( $settings->woo_product_orders_category ) ) {
				$query_args['woo_product_orders'] = array();
				$query_args['woo_product_orders_category'] = array();

				if ( ! empty( $settings->woo_product_orders ) ) {
					if ( is_array( $settings->woo_product_orders ) ) {
						$query_args['woo_product_orders'] = $settings->woo_product_orders;
					} else {
						$query_args['woo_product_orders'][] = $settings->woo_product_orders;
					}
				}
				if ( ! empty( $settings->woo_product_orders_category ) ) {
					if ( is_array( $settings->woo_product_orders_category ) ) {
						$query_args['woo_product_orders_category'] = $settings->woo_product_orders_category;
					} else {
						$query_args['woo_product_orders_category'][] = $settings->woo_product_orders_category;
					}
				}
			} else {
				$query_args = array();
				//$orders = IBX_WPFomo_WooCommerce_Helper::get_orders( $settings->display_last_days, $settings->display_last, true );
			}
			$orders = IBX_WPFomo_WooCommerce_Helper::get_orders_by_product( $query_args, $settings->display_last_days, $settings->display_last );

			if ( $orders && ! empty( $orders ) ) {
				// Store data in transient.
				set_transient( $transient_key, $orders, ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
			} else {
				return array();
			}
		}

		return $orders;
	}

	public function add_conversion_analytics_source( $sources ) {
		$sources['woo'] = 'WooCommerce';

		return $sources;
	}

	public function add_conversion_count( $count, $settings ) {
		if ( 'woo' == $settings->conversion_analytics_source ) {
			$data = $this->get_data( $settings->post_id, $settings );
			$count = count( $data );
		}

		return $count;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_WooCommerce object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_WooCommerce ) ) {
			self::$instance = new IBX_WPFomo_WooCommerce();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_woocommerce = IBX_WPFomo_WooCommerce::get_instance();
