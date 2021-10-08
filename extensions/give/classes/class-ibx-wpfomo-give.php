<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_Give {
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
		require_once IBX_WPFOMO_GIVE_DIR . 'classes/class-ibx-wpfomo-give-helper.php';

		add_action( 'ibx_wpfomo_before_metabox_load', array( $this, 'init_fields' ) );
		add_filter( 'ibx_wpfomo_conversion_data', array( $this, 'add_conversion_data' ), 10, 2 );
	}

	/**
	 * Clears transients on post update.
	 *
	 * @since 1.0.1
	 */
	public function clear_transients( $post_id ) {
		if ( isset( $_POST['ibx_wpfomo_conversions_source'] ) && 'give' == $_POST['ibx_wpfomo_conversions_source'] ) {
			delete_transient( 'ibx_wpfomo_give_data_' . $post_id );
		}
	}

	/**
	 * Updates the meta for the new fields introduced in WPfomify 2.0
	 *
	 * @since 1.0.2
	 */
	public function update_meta( $data ) {
		$data['give'] = array(
			'condition'	=> array(
				'ibx_wpfomo_type'				=> 'conversion',
				'ibx_wpfomo_conversions_source'	=> 'give',
			),
			'fields'	=> array(
				'ibx_wpfomo_give_form_img' 	=> array( 'ibx_wpfomo_product_img' ),
				'ibx_wpfomo_give_donations'	=> array( 'ibx_wpfomo_display_last' ),
			),
		);

		return $data;
	}

	/**
	 * Init fields for Give integration.
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
			'give_template'	=> array(
				'type'			=> 'template',
				'label'			=> __( 'Notification Template', 'ibx-wpfomo' ),
				'default'		=> array(
					'0'				=> __( '{{name}} donated for', 'ibx-wpfomo' ),
					'1'				=> '{{title}}',
					'2'				=> '{{time}}',
				),
				'variables'		=> array( '{{name}}', '{{title}}', '{{time}}' ),
				'sanitize'		=> false,
			),
			'give_form_donations'   => array(
				'type'                  => 'select',
				'label'                 => __( 'Display Donations of', 'ibx-wpfomo' ),
				'default'               => '',
				'options'               => IBX_WPFomo_Give_Helper::get_forms_list(),
			),
			'give_custom_url'       => array(
				'type'                  => 'text',
				'label'                 => __( 'Link to', 'ibx-wpfomo' ),
				'default'               => '',
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
	 * Add Give as conversion source.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function conversion_source( $data ) {
		$data['options']['give'] = 'Give';
		$data['toggle']['give']['fields'] = array_keys( $this->get_fields() );
		$data['toggle']['give']['fields'][] = 'product_img';

		return $data;
	}

	/**
	 * Add fields for Give conversion.
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
	 * Add conversion content for Give.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( 'give' != $settings->conversions_source ) {
			return $data;
		}

		$transient_key = 'ibx_wpfomo_give_data_' . $settings->post_id;

		// get cache duration from settings.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		$payments = get_transient( $transient_key );

		if ( ! $payments || empty( $payments ) ) {
			if ( '' != $settings->give_form_donations ) {
				$payments = IBX_WPFomo_Give_Helper::get_payments_by_form( $settings->give_form_donations, $settings->display_last_days, $settings->display_last );
			} else {
				$payments = IBX_WPFomo_Give_Helper::get_payments( $settings->give_donations, $settings->display_last );
			}

			if ( $payments && ! empty( $payments ) ) {
				// Store data in transient for 6 hours.
				set_transient( $transient_key, $payments, ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
			} else {
				return $data;
			}
		}

		foreach ( $payments as $payment ) {

			if ( '' != $settings->give_form_donations ) {
				$form_id = $settings->give_form_donations;
			} else {
				// Get forms from payment.
				$form_id = IBX_WPFomo_Give_Helper::get_payment_form_id( $payment->ID );
			}

			if ( ! $form_id || empty( $form_id ) ) {
				continue;
			}

			$form_name = get_the_title( $form_id );

			// Get the user details.
			$user = IBX_WPFomo_Give_Helper::get_payment_user( $payment->ID );

			// Data to render notification.
			$fields_data = array(
				'title'     => $form_name,
				'name'      => $user['name'],
				'email'     => $user['email'],
			);

			$time = IBX_WPFomo_Give_Helper::get_payment_date( $payment->ID );

			if ( $time ) {
				$fields_data['time'] = IBX_WPFomo_Helper::get_timeago_html( $time );
			}

			// Link
			$fields_data['url'] = esc_url( $settings->give_custom_url );

			// Form image.
			if ( isset( $settings->product_img ) && $settings->product_img ) {
				$product_image = wp_get_attachment_image_src( get_post_thumbnail_id( $form_id ), 'thumbnail' );

				if ( ! empty( $product_image ) ) {
					$fields_data['image'] = array(
						'url'   => $product_image[0],
					);
				}
			}

			$data['fields'][] = $fields_data;
		} // End foreach().

		$data['template'] = $settings->give_template;

		return $data;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_Give object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Give ) ) {
			self::$instance = new IBX_WPFomo_Give();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_give = IBX_WPFomo_Give::get_instance();
