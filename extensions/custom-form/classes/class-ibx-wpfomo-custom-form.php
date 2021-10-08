<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_Custom_Form extends IBX_WPFomo_Addon {
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
		parent::__construct( array(
			'name'  => 'Custom Form URL',
			'slug'  => 'custom_form_url',
			'dir'   => IBX_WPFOMO_CUSTOM_FORM_DIR,
			'url'   => IBX_WPFOMO_CUSTOM_FORM_URL,
		) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	}

	/**
	 * Enqueue frontend style and scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-custom-form-script', $this->url . 'assets/js/frontend-custom-form.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
	}

	/**
	 * Conversion fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function fields() {
		$fields = array(
			'configuration'	=> array(
				'custom_form_url'	=> array(
					'title'	=> __( 'Custom Form URL', 'ibx-wpfomo' ),
					'description' => __( 'Get conversions from forms availaible on a page.', 'ibx-wpfomo' ),
					'insert_after'	=> 'config',
					'hide_if'	=> '!conversion',
					'fields'	=> array(
						'custom_form_url'          => array(
							'type'        => 'url',
							'label'       => __( 'Page URL', 'ibx-wpfomo' ),
							'placeholder' => '',
							'default'     => '',
							'help'        => __( 'Add URL of a page that contains form(s).', 'ibx-wpfomo' ),
							'priority'    => 150,
						),
						'custom_form_url_submit'   => array(
							'type'     => 'button',
							'label'    => __( 'Get Forms', 'ibx-wpfomo' ),
							'class'    => 'ibx_wpfomo_custom_form_url_submit',
							'priority' => 200,
						),
						'custom_form_title'        => array(
							'type'        => 'text',
							'label'       => __( 'Title', 'ibx-wpfomo' ),
							'placeholder' => __( 'Title', 'ibx-wpfomo' ),
							'default'     => __( 'Form', 'ibx-wpfomo' ),
							'required'    => true,
							'priority'    => 250,
						),
						'custom_form_parsed'       => array(
							'type'     => 'hidden',
							'default'  => '',
							'hidden'	=> true,
							'priority' => 300,
						),
						'custom_form_select'       => array(
							'type'     => 'select',
							'label'    => __( 'Select Form', 'ibx-wpfomo' ),
							'default'  => '',
							'priority' => 350,
						),
						'custom_form_name_select'  => array(
							'type'     => 'select',
							'label'    => __( 'Select Name Field', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 400,
						),
						'custom_form_email_select' => array(
							'type'     => 'select',
							'label'    => __( 'Select Email Field', 'ibx-wpfomo' ),
							'default'  => '',
							'priority' => 450,
						),
						'custom_form_src_post'     => array(
							'type'     => 'hidden',
							'default'  => '',
							'hidden'	=> true,
							'priority' => 500,
						),
						'custom_form_unique_key_attr' => array(
							'type'     => 'hidden',
							'default'  => '',
							'priority' => 550,
						),
					),
				),
			),
			'content'	=> array(
				'content_section'	=> array(
					'notification_custom_form_msg' => array(
						'type'      => 'template',
						'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
						'default'   => array(
							'0' => __( '{{name}} signed up for', 'ibx-wpfomo' ),
							'1' => '{{title}}',
							'2' => '{{time}}',
						),
						'variables' => array( '{{title}}', '{{name}}', '{{time}}' ),
						'sanitize'  => false,
						'priority'  => 50,
					),
					'custom_form_target_url' => array(
						'type'    => 'text',
						'label'   => __( 'Link Notification to', 'ibx-wpfomo' ),
						'default' => '',
					),
					'custom_form_entries'    => array(
						'type'  => 'button',
						'label' => __( 'Entries', 'ibx-wpfomo' ),
						'text'  => __( 'Show Entries', 'ibx-wpfomo' ),
						'url'   => IBX_WPFomo_List_Helper::get_button_url(),
					),
				),
			),
		);

		return $fields;
	}

	public function add_conversion_data( $data, $settings ) {
		if ( $this->slug != $settings->conversions_source ) {
			return $data;
		}

		$id = isset( $id ) ? $id : $settings->post_id;

		$display_last      = $settings->display_last;
		$display_last_days = $settings->display_last_days;

		$where_condition = '';
		$limit_condition = '';

		if ( ! empty( $display_last ) ) {
			$limit_condition = " LIMIT $display_last";
		}

		if ( ! empty( $display_last_days ) ) {
			$where_condition = " AND src NOT IN ('csv') AND time >= ( CURDATE() - INTERVAL $display_last_days DAY )";
		}

		$order_by			= '';
		$conversion_data	= IBX_WPFomo_Conversion::get_conversion_data( $id, $where_condition, $order_by, $limit_condition );
		$filter_data		= array();

		if ( ! empty( $conversion_data ) ) {
			foreach ( $conversion_data as $conversion ) {
				$email = $conversion['email'];
				$filter_data[ $email ] = $conversion;
			}
		}

		$data['fields']   	= $filter_data;
		$data['template'] 	= $settings->notification_custom_form_msg;

		return $data;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_Custom_Form object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Custom_Form ) ) {
			self::$instance = new IBX_WPFomo_Custom_Form();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_custom_form = IBX_WPFomo_Custom_Form::get_instance();
