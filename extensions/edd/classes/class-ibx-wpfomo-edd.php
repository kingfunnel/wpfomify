<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_EDD {
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
		add_action( 'ibx_wpfomo_save_post', array( $this, 'clear_transients' ), 10, 3 );
	}

	/**
	 * Initialize the hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		require_once IBX_WPFOMO_EDD_DIR . 'classes/class-ibx-wpfomo-edd-helper.php';

		add_action( 'ibx_wpfomo_before_metabox_load', array( $this, 'init_fields' ) );
		add_filter( 'ibx_wpfomo_conversion_data', array( $this, 'add_conversion_data' ), 10, 2 );
		add_filter( 'ibx_wpfomo_conversion_analytics_source', array( $this, 'add_conversion_analytics_source' ) );
		add_filter( 'ibx_wpfomo_conversion_count', array( $this, 'add_conversion_count' ), 10, 2 );
	}

	/**
	 * Clears transients on post update.
	 *
	 * @since 1.0.1
	 */
	public function clear_transients( $post_id, $prefix, $settings ) {
		if ( isset( $_POST['ibx_wpfomo_conversions_source'] ) && 'edd' == $_POST['ibx_wpfomo_conversions_source'] ) {
			delete_transient( 'ibx_wpfomo_edd_orders_' . $post_id );

			// cache data.
			$this->get_data( $post_id, $settings );
		}
	}

	/**
	 * Updates the meta for the new fields introduced in WPfomify 2.0
	 *
	 * @since 1.0.2
	 */
	public function update_meta( $data ) {
		$data['edd'] = array(
			'condition' => array(
				'ibx_wpfomo_type'               => 'conversion',
				'ibx_wpfomo_conversions_source' => 'edd',
			),
			'fields'    => array(
				'ibx_wpfomo_edd_product_img' => array( 'ibx_wpfomo_product_img' ),
				'ibx_wpfomo_edd_orders'      => array( 'ibx_wpfomo_display_last' ),
			),
		);

		return $data;
	}

	/**
	 * Init fields for EDD integration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_fields() {
		 add_filter( 'ibx_wpfomo_field_fomo_type', array( $this, 'hide_fields' ), 10, 1 );
		add_filter( 'ibx_wpfomo_field_conversions_source', array( $this, 'conversion_source' ), 10, 1 );
		add_filter( 'ibx_wpfomo_metabox_fields', array( $this, 'add_fields' ), 10, 1 );
	}

	/**
	 * Conversion fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_fields() {
		$fields = array(
			'edd_template'       => array(
				'type'      => 'template',
				'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
				'default'   => array(
					'0' => __( '{{name}} purchased', 'ibx-wpfomo' ),
					'1' => '{{title}}',
					'2' => '{{time}}',
				),
				'variables' => array( '{{name}}', '{{title}}', '{{time}}', '{{city}}', '{{state}}', '{{country}}' ),
				'sanitize'  => false,
			),
			'edd_product_orders' => array(
				'type'    => 'select',
				'label'   => __( 'Show purchase of', 'ibx-wpfomo' ),
				'default' => '',
				'render'  => IBX_WPFomo_Helper::is_pro_version(),
				'options' => IBX_WPFomo_EDD_Helper::get_products_list(),
			),
			'edd_product_link'   => array(
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
						'fields' => array( 'edd_custom_url' ),
					),
				),
			),
			'edd_custom_url'     => array(
				'type'    => 'text',
				'label'   => __( 'Custom URL', 'ibx-wpfomo' ),
				'default' => '',
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
			if ( 'conversion' != $option_key ) {
				foreach ( $fields as $key => $field ) {
					$data['hide'][ $option_key ]['fields'][] = $key;
				}
			}
		}

		return $data;
	}

	/**
	 * Add EDD as conversion source.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function conversion_source( $data ) {
		$data['options']['edd']            = 'Easy Digital Downloads';
		$data['toggle']['edd']['fields']   = array_keys( $this->get_fields() );
		$data['toggle']['edd']['fields'][] = 'product_img';
		$data['hide']['custom']['fields']  = array( 'edd_custom_url' );

		return $data;
	}

	/**
	 * Add fields for EDD conversion.
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
	 * Add conversion content for EDD.
	 *
	 * @since 1.0.0
	 * @param array  $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( 'edd' != $settings->conversions_source ) {
			return $data;
		}

		$orders = $this->get_data( $settings->post_id, $settings );

		if ( empty( $orders ) ) {
			return $data;
		}

		foreach ( $orders as $order_post ) {

			if ( isset( $settings->edd_product_orders ) && '' != $settings->edd_product_orders ) {
				$product[] = array(
					'id' => $settings->edd_product_orders,
				);
			} else {
				// Get a product from order.
				$product = IBX_WPFomo_EDD_Helper::get_payment_downloads( $order_post->ID );
			}

			if ( ! $product || empty( $product ) ) {
				continue;
			}

			$product      = $product[0];
			$product_name = get_the_title( $product['id'] );

			// Get the user details.
			$user       = IBX_WPFomo_EDD_Helper::get_payment_user( $order_post->ID );
			$ip_address = IBX_WPFomo_EDD_Helper::get_customer_ip( $order_post->ID );

			$location = IBX_WPFomo_Helper::get_location_from_ip( $ip_address );

			// Data to render notification.
			$fields_data = array(
				'title'   => $product_name,
				'name'    => $user['name'],
				'email'   => $user['email'],
				'city'    => $location['city'],
				'state'   => $location['state'],
				'country' => $location['country'],
			);

			$time = IBX_WPFomo_EDD_Helper::get_payment_date( $order_post->ID );

			if ( $time ) {
				$fields_data['time'] = IBX_WPFomo_Helper::get_timeago_html( $time );
			}

			// Product URL or Custom URL.
			if ( 'product' == $settings->edd_product_link ) {
				$fields_data['url'] = get_permalink( $product['id'] );
			}
			if ( 'custom' == $settings->edd_product_link ) {
				$fields_data['url'] = esc_url( $settings->edd_custom_url );
			}

			// Product image.
			if ( isset( $settings->product_img ) && $settings->product_img ) {
				$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product['id'] ), 'thumbnail' );

				if ( ! empty( $product_image ) ) {
					$fields_data['image'] = array(
						'url' => $product_image[0],
					);
				}
			}

			$data['fields'][] = $fields_data;
		} // End foreach().

		$data['template'] = $settings->edd_template;

		return $data;
	}

	protected function get_data( $post_id, $settings = null ) {
		$settings = empty( $settings ) ? MetaBox_Tabs::get_metabox_settings( $post_id ) : $settings;

		$transient_key = 'ibx_wpfomo_edd_orders_' . $post_id;

		// get cache duration from settings.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		$orders = get_transient( $transient_key );

		if ( ! $orders || empty( $orders ) ) {
			if ( isset( $settings->edd_product_orders ) && '' != $settings->edd_product_orders ) {
				$orders = IBX_WPFomo_EDD_Helper::get_payments_by_product( $settings->edd_product_orders, $settings->display_last_days, $settings->display_last );
			} else {
				$orders = IBX_WPFomo_EDD_Helper::get_payments( $settings->display_last_days, $settings->display_last );
			}

			if ( $orders && ! empty( $orders ) ) {
				// Store data in transient for 6 hours.
				set_transient( $transient_key, $orders, ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
			} else {
				return array();
			}
		}

		return $orders;
	}

	public function add_conversion_analytics_source( $sources ) {
		$sources['edd'] = 'Easy Digital Downloads';

		return $sources;
	}

	public function add_conversion_count( $count, $settings ) {
		if ( 'edd' == $settings->conversion_analytics_source ) {
			$data = $this->get_data( $settings->post_id, $settings );
			$count = count( $data );
		}

		return $count;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_EDD object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_EDD ) ) {
			self::$instance = new IBX_WPFomo_EDD();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_edd = IBX_WPFomo_EDD::get_instance();
