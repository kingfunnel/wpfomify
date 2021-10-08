<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_LLMS {
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
	}

	/**
	 * Initialize the hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_hooks() {
		require_once IBX_WPFOMO_LLMS_DIR . 'classes/class-ibx-wpfomo-llms-helper.php';

		add_action( 'ibx_wpfomo_before_metabox_load', array( $this, 'init_fields' ) );
		add_filter( 'ibx_wpfomo_conversion_data', array( $this, 'add_conversion_data' ), 10, 2 );
	}

	/**
	 * Clears transients on post update.
	 *
	 * @since 1.0.1
	 */
	public function clear_transients( $post_id ) {
		if ( isset( $_POST['ibx_wpfomo_conversions_source'] ) && 'llms' == $_POST['ibx_wpfomo_conversions_source'] ) {
			delete_transient( 'ibx_wpfomo_llms_orders_' . $post_id );
		}
	}

	/**
	 * Updates the meta for the new fields introduced in WPfomify 2.0
	 *
	 * @since 1.0.1
	 */
	public function update_meta( $data ) {
		$data['llms'] = array(
			'condition'	=> array(
				'ibx_wpfomo_type'				=> 'conversion',
				'ibx_wpfomo_conversions_source'	=> 'llms',
			),
			'fields'	=> array(
				'ibx_wpfomo_llms_course_img' 	=> array( 'ibx_wpfomo_product_img' ),
				'ibx_wpfomo_llms_course_orders'	=> array( 'ibx_wpfomo_display_last' ),
			),
		);

		return $data;
	}

	/**
	 * Init fields for LifterLMS integration.
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
			'llms_template'	=> array(
				'type'			=> 'template',
				'label'			=> __( 'Notification Template', 'ibx-wpfomo' ),
				'default'		=> array(
					'0'				=> __( '{{name}} from {{country}} enrolled for', 'ibx-wpfomo' ),
					'1'				=> '{{title}}',
					'2'				=> '{{time}}',
				),
				'variables'		=> array( '{{name}}', '{{city}}', '{{state}}', '{{country}}', '{{title}}', '{{time}}' ),
				'sanitize'		=> false,
			),
			'llms_course_orders'    => array(
				'type'      => 'select',
				'label'     => __( 'Display Orders of', 'ibx-wpfomo' ),
				'default'   => '',
				'options'   => IBX_WPFomo_LLMS_Helper::get_display_orders_list(),
			),
			'llms_course_link'  => array(
				'type'      => 'select',
				'label'     => __( 'Link Notification to', 'ibx-wpfomo' ),
				'default'   => 'none',
				'options'   => array(
					'none'      => __( 'None', 'ibx-wpfomo' ),
					'course'    => __( 'Course Page', 'ibx-wpfomo' ),
					'custom'    => __( 'Custom URL', 'ibx-wpfomo' ),
				),
				'help'      => __( 'You can link notification with course page or custom URL.', 'ibx-wpfomo' ),
				'toggle'    => array(
					'custom'    => array(
						'fields'    => array( 'llms_custom_url' ),
					),
				),
			),
			'llms_custom_url'    => array(
				'type'      => 'text',
				'label'     => __( 'Custom URL', 'ibx-wpfomo' ),
				'default'   => '',
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
	 * Add LifterLMS as conversion source.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function conversion_source( $data ) {
		$data['options']['llms'] = 'LifterLMS';
		$data['toggle']['llms']['fields'] = array_keys( $this->get_fields() );
		$data['toggle']['llms']['fields'][] = 'product_img';
		$data['hide']['custom']['fields'] = array( 'llms_custom_url' );

		return $data;
	}

	/**
	 * Add fields for LifterLMS conversion.
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
	 * Add conversion content for LifterLMS.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( 'llms' != $settings->conversions_source ) {
			return $data;
		}

		$transient_key = 'ibx_wpfomo_llms_orders_' . $settings->post_id;

		// get cache duration from settings.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		$orders = get_transient( $transient_key );

		if ( ! $orders || empty( $orders ) ) {
			if ( '' != $settings->llms_course_orders ) {
				$orders = IBX_WPFomo_LLMS_Helper::get_orders_by_course( $settings->llms_course_orders, $settings->display_last_days, $settings->display_last );
			} else {
				$orders = IBX_WPFomo_LLMS_Helper::get_orders( $settings->display_last_days, $settings->display_last, true );
			}

			if ( $orders && ! empty( $orders ) ) {
				// Store data in transient.
				set_transient( $transient_key, $orders, ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
			} else {
				return $data;
			}
		}

		foreach ( $orders as $order_post ) {

			if ( '' != $settings->llms_course_orders ) {
				$course[] = array(
					'id' => $settings->llms_course_orders,
					'title' => get_the_title( $settings->llms_course_orders ),
				);
			} else {
				// Get course from order.
				$course = IBX_WPFomo_LLMS_Helper::get_order_course( $order_post );
			}

			if ( ! $course || empty( $course ) ) {
				continue;
			}

			$course = $course[0];
			$course_name = $course['title'];

			// Get the billing details.
			$billing = IBX_WPFomo_LLMS_Helper::get_order_customer( $order_post );
			$billing_name = $billing['first_name'];

			if ( empty( $billing_name ) ) {
				$billing_name = IBX_WPFomo_Helper::get_someone_translation();
			} else {
				if ( isset( $billing['last_name'] ) && ! empty( $billing['last_name'] ) ) {
					$billing_name .= ' ' . $billing['last_name'][0] . '.';
				}
			}

			// Data to render notification.
			$fields_data = array(
				'title'     => $course_name,
				'name'      => $billing_name,
				'email'     => $billing['email'],
				'city'      => $billing['city'],
				'state'     => $billing['state'],
				'country'   => $billing['country'],
			);

			if ( ! empty( $billing['time'] ) && $billing['time'] ) {
				$fields_data['time'] = IBX_WPFomo_Helper::get_timeago_html( $billing['time'] );
			}

			// Product URL or Custom URL.
			if ( 'course' == $settings->llms_course_link ) {
				$fields_data['url'] = get_permalink( $course['id'] );
			}
			if ( 'custom' == $settings->llms_course_link ) {
				$fields_data['url'] = esc_url( $settings->llms_custom_url );
			}

			// Product image.
			if ( isset( $settings->product_img ) && $settings->product_img ) {
				$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $course['id'] ), 'thumbnail' );

				if ( ! empty( $product_image ) ) {
					$fields_data['image'] = array(
						'url'   => $product_image[0],
					);
				}
			}

			$data['fields'][] = $fields_data;
		} // End foreach().

		$data['template'] = $settings->llms_template;

		return $data;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_LLMS object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_LLMS ) ) {
			self::$instance = new IBX_WPFomo_LLMS();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_llms = IBX_WPFomo_LLMS::get_instance();
