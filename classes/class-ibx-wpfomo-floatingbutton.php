<?php

class IBX_WPFomo_Floating_Button {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Holds the fomo type.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $type = 'floating_button';

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init_hooks' ) );
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks() {
		add_action( 'ibx_wpfomo_before_metabox_load', array( $this, 'init_fields' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'ibx_wpfomo_frontend_render_content', array( $this, 'render_content' ), 10, 2 );
		add_filter( 'ibx_wpfomo_admin_preview', array( $this, 'render_admin_preview' ) );
	}

	/**
	 * Init fields for Floating Button integration.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init_fields() {
		add_filter( 'ibx_wpfomo_types', array( $this, 'add_type' ), 10, 1 );
		add_filter( 'ibx_wpfomo_field_fomo_type', array( $this, 'field_config' ), 0, 1 );
		add_filter( 'ibx_wpfomo_metabox_fields', array( $this, 'render_fields' ), 0, 1 );
	}

	/**
	 * Enqueue style and scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Floating Button fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_fields() {
		$fields = array(
			'fb_icon_source'   => array(
				'type'      => 'select',
				'label'     => __( 'Icon', 'ibx-wpfomo' ),
				'default'   => 'default',
				'options'   => array(
					'default'   => __( 'Default', 'ibx-wpfomo' ),
					'dashicons' => __( 'Dashicons', 'ibx-wpfomo' ),
					'custom'    => __( 'Custom', 'ibx-wpfomo' ),
				),
				'toggle'    => array(
					'dashicons' => array(
						'fields'    => array( 'fb_dashicons' ),
					),
					'custom'    => array(
						'fields'    => array( 'fb_icon_custom' ),
					),
				),
			),
			'fb_dashicons'   => array(
				'type'          => 'text',
				'label'         => __( 'Dashicons CSS Class', 'ibx-wpfomo' ),
				'default'       => '',
				// translators: %s is for HTML attributes.
				'help'          => sprintf( __( 'For example, dashicons-format-chat. <a%s>Click here</a> to get the list of dashicons.', 'ibx-wpfomo' ), ' href="https://developer.wordpress.org/resource/dashicons/#format-chat" target="_blank"' ),
			),
			'fb_icon_custom'    => array(
				'type'              => 'photo',
				'label'             => __( 'Custom Icon', 'ibx-wpfomo' ),
			),
			'fb_header'         => array(
				'type'              => 'editor',
				'label'             => __( 'Header', 'ibx-wpfomo' ),
				'default'           => '',
				'rows'              => 5,
				'media_buttons'     => false,
				'drag_drop_upload'  => false,
			),
			'fb_content'        => array(
				'type'              => 'editor',
				'label'             => __( 'Content', 'ibx-wpfomo' ),
				'default'           => '',
				'rows'              => 5,
			),
		);

		return $fields;
	}

	/**
	 * Add Floating Button as new fomo type.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function add_type( $types ) {
		$types[ $this->type ] = __( 'Floating Button', 'ibx-wpfomo' );

		return $types;
	}

	/**
	 * Configuration for new field.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function field_config( $data ) {
		$fields = $this->get_fields();
		$tmp_fields = $fields;

		unset( $tmp_fields['fb_dashicons'] );
		unset( $tmp_fields['fb_icon_custom'] );

		$field_ids = array_keys( $tmp_fields );

		$field_ids[] = 'fb_position';
		$field_ids[] = 'fb_button_bg_color';
		$field_ids[] = 'fb_button_text_color';
		$field_ids[] = 'fb_header_bg_color';
		$field_ids[] = 'fb_header_text_color';
		$field_ids[] = 'fb_show_popup_always';

		$data['toggle'][ $this->type ] = array(
			'fields'    => $field_ids,
		);

		$data['hide'][ $this->type ] = array(
			'fields'    => array( 'closable', 'notification_msg', 'conversion_group' ),
		);

		// Hide floating button's fields from other field types.
		foreach ( $data['options'] as $option_key => $option_val ) {
			if ( $option_key != $this->type ) {
				foreach ( $fields as $key => $field ) {
					$data['hide'][ $option_key ]['fields'][] = $key;
				}
			}
		}

		return $data;
	}

	/**
	 * Render fields for Floating Button.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @return array
	 */
	public function render_fields( $data ) {
		$fields = $this->get_fields();

		// Add fields to Content section in Content tab.
		foreach ( $fields as $key => $field ) {
			$data['content']['sections']['content_section']['fields'][ $key ] = $field;
		}

		// Add fields to Design section in Design tab.
		$data['customize']['sections']['appearance']['fields']['fb_position'] = array(
			'type'          => 'select',
			'label'         => __( 'Position', 'ibx-wpfomo' ),
			'default'       => 'bottom-right',
			'options'       => array(
				'bottom-left'   => __( 'Bottom Left', 'ibx-wpfomo' ),
				'bottom-right'  => __( 'Bottom Right', 'ibx-wpfomo' ),
			),
			'priority'		=> 11.1,
		);

		$data['customize']['sections']['appearance']['fields']['fb_show_popup_always'] = array(
			'type'              => 'checkbox',
			'label'             => __( 'Always show content popup', 'ibx-wpfomo' ),
			'default'           => '0',
			'sanitize'          => false,
			'description'       => __( 'If checked, the content popup box will be displayed everytime.', 'ibx-wpfomo' ),
		);

		$data['customize']['sections']['design']['fields']['fb_button_bg_color'] = array(
			'type'          => 'color',
			'label'         => __( 'Button Background Color', 'ibx-wpfomo' ),
			'default'       => '#2477f4',
			'priority'		=> 1,
		);

		$data['customize']['sections']['design']['fields']['fb_button_text_color'] = array(
			'type'          => 'color',
			'label'         => __( 'Button Icon Color', 'ibx-wpfomo' ),
			'default'       => '#ffffff',
			'priority'		=> 2,
		);

		$data['customize']['sections']['design']['fields']['fb_header_bg_color'] = array(
			'type'          => 'color',
			'label'         => __( 'Header Background Color', 'ibx-wpfomo' ),
			'default'       => '#2477f4',
			'priority'		=> 3,
		);

		$data['customize']['sections']['design']['fields']['fb_header_text_color'] = array(
			'type'          => 'color',
			'label'         => __( 'Header Text Color', 'ibx-wpfomo' ),
			'default'       => '#ffffff',
			'priority'		=> 4,
		);

		return $data;
	}

	/**
	 * Render floating button content.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_content( $type, $settings ) {
		if ( $type == $this->type ) {
			include IBX_WPFOMO_DIR . 'includes/floating-button.php';
		}
	}

	/**
	 * Render floating button preview.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_admin_preview( $settings ) {
		include IBX_WPFOMO_DIR . 'includes/floating-button.php';
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_Floating_Button object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Floating_Button ) ) {
			self::$instance = new IBX_WPFomo_Floating_Button();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_floating_button = IBX_WPFomo_Floating_Button::get_instance();
