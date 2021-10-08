<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_Envato extends IBX_WPFomo_Addon {
	/**
	 * Holds the class object.
	 *
	 * @since 2.1
	 * @var object
	 */
	public static $instance;

	/**
	 * Primary class constructor
	 *
	 * @since 2.1
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name' => 'Envato',
				'slug' => 'envato',
				'dir'  => IBX_WPFOMO_ENVATO_DIR,
				'url'  => IBX_WPFOMO_ENVATO_URL,
			)
		);

		require_once IBX_WPFOMO_ENVATO_DIR . 'classes/class-ibx-wpfomo-envato-helper.php';

		add_action( 'wp_ajax_ibx_wpfomo_connect_envato', array( $this, 'connect_envato' ) );
		add_action( 'ibx_wpfomo_admin_settings_scripts', array( $this, 'admin_settings_scripts' ) );
		add_filter( 'mbt_filter_suggest_field_data', array( $this, 'item_list_suggest_data' ), 10, 2 );
		add_action( 'admin_notices', array( $this, 'render_admin_notices' ) );
		add_action( 'ibx_wpfomo_save_post', array( $this, 'cache_data' ), 10, 3 );
	}

	/**
	 * Enqueue scripts in admin.
	 *
	 * @since 2.1
	 */
	public function admin_settings_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-envato-settings', $this->url . 'assets/js/main.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
		wp_localize_script(
			'ibx-wpfomo-envato-settings',
			'wpfomo_envato',
			array(
				'messages' => array(
					'connect_success' => __( 'Connected successfully!', 'ibx-wpfomo' ),
				),
			)
		);
	}

	/**
	 * Render notices.
	 *
	 * @since 2.1
	 */
	public function render_admin_notices() {
		echo IBX_WPFomo_Helper::get_notice();
	}

	/**
	 * Update items sales in DB.
	 *
	 * @since 2.1
	 * @param int $post_id
	 * @param string $prefix
	 * @param object $settings
	 * @return array
	 */
	public function cache_data( $post_id, $prefix, $settings ) {
		return $this->get_envato_sales( $post_id, $settings );
	}

	/**
	 * Get lists from saved option.
	 *
	 * @since 2.1
	 * @return array
	 */
	public function get_item_list() {
		$options = array();
		$items = get_option( 'ibx_wpfomo_envato_items' );

		if ( $items ) {
			$items = maybe_unserialize( $items );
		}

		if ( ! is_array( $items ) || count( $items ) < 1 ) {
			return $options;
		}

		if ( isset( $items['error'] ) && $items['error'] ) {
			return $options;
		}

		foreach ( $items as $item_id => $item_name ) {
			$options[ $item_id ] = $item_name;
		}

		return $options;
	}

	/**
	 * Get sales from saved data.
	 *
	 * @since 2.1
	 * @return array
	 */
	public function get_envato_sales( $post_id, $settings = null ) {
		$settings = empty( $settings ) ? MetaBox_Tabs::get_metabox_settings( $post_id ) : $settings;

		if ( 'conversion' != $settings->type ) {
			return;
		}

		if ( $this->slug != $settings->conversions_source ) {
			return;
		}

		$transient_key = 'ibx_wpfomo_envato_sales_' . $post_id;

		$sales = IBX_WPFomo_Envato_Helper::get_items_sales();

		if ( isset( $sales['error'] ) && $sales['error'] ) {
			IBX_WPFomo_Helper::set_notice( $sales['error'], 'error' );
			return;
		}

		$item_ids = $settings->envato_order_item;

		if ( empty( $item_ids ) ) {
			IBX_WPFomo_Helper::set_notice( __( 'Error: You must select at least one item as "Show purchase of"', 'ibx-wpfomo' ), 'error' );
			return;
		}

		$limit = $settings->display_last;

		// Set limit to 100 if empty.
		if ( empty( $limit ) || ! $limit ) {
			$limit = 100;
		}

		$display_last_days = $settings->display_last_days;

		// Set display_last_days to 100 if empty.
		if ( empty( $display_last_days ) || ! $display_last_days ) {
			$display_last_days = 100;
		}

		$current_time = strtotime( date( 'Y-m-d H:i:s' ) );
		$items = array();

		foreach ( $sales as $sale ) {
			if ( ! in_array( $sale['id'], $item_ids ) ) {
				continue;
			}

			if ( 'custom' === $settings->envato_item_link ) {
				$sale['url'] = $settings->envato_custom_url;
			}

			$timestamp = strtotime( $sale['time'] );

			$sale['time'] = IBX_WPFomo_Helper::get_timeago_html( date( 'Y-m-d H:i:s', strtotime( $sale['time'] ) ) );

			$items[ $timestamp ] = $sale;
		}

		krsort( $items );

		$fields = array_filter(
			$items,
			function ( $timestamp ) use ( $display_last_days, $current_time ) {
				$diff = $current_time - $timestamp;
				$diff = round( $diff / ( 60 * 60 * 24 ) );
				return ( $display_last_days >= $diff );
			},
			ARRAY_FILTER_USE_KEY
		);

		$fields = array_slice( $fields, 0, $limit );

		$data = array(
			'fields'   => $fields,
			'template' => $settings->envato_template,
		);

		IBX_WPFomo_Helper::set_cache_data( $transient_key, $data );

		return $data;
	}

	/**
	 * Register admin settings.
	 *
	 * @since 2.1
	 * @param array $settings
	 * @return array
	 */
	public function register_admin_settings( $settings ) {
		$settings['sections']['envato_personal_token'] = array(
			'title'  => 'Envato',
			'fields' => array(
				'envato_personal_token' => array(
					'type'    => 'text',
					'label'   => __( 'Personal Token', 'ibx-wpfomo' ),
					'default' => '',
					'help'    => __( 'To get your Envato access token, <a href="https://wpfomify.com/docs/sections/integrations/display-sales-notifications-envato/" target="_blank">click here</a>', 'ibx-wpfomo' ),
				),
				'envato_connect'        => array(
					'type'  => 'button',
					'label'	=> '&nbsp;',
					'text' => __( 'Connect', 'ibx-wpfomo' ),
					'class' => 'ibx-wpfomo-envato-connect',
				),
			),
		);

		return $settings;
	}
	/**
	 * Conversion fields.
	 *
	 * @since 2.1
	 * @return array
	 */
	public function fields() {
		$fields = array(
			'content'	=> array(
				'content_section'	=> array(
					'envato_template'   => array(
						'type'      => 'template',
						'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
						'default'   => array(
							'0' => __( 'Someone purchased', 'ibx-wpfomo' ),
							'1' => '{{title}}',
							'2' => '{{time}}',
						),
						'variables' => array( '{{title}}', '{{time}}' ),
						'sanitize'  => false,
					),
					'envato_order_item' => array(
						'type'        => 'suggest',
						'label'       => __( 'Show purchase of', 'ibx-wpfomo' ),
						'placeholder' => __( 'Select item(s)', 'ibx-wpfomo' ),
						'default'     => '',
						'action'      => 'get_envato_item_list',
					),
					'envato_item_link'  => array(
						'type'    => 'select',
						'label'   => __( 'Link Notification to', 'ibx-wpfomo' ),
						'default' => 'none',
						'options' => array(
							'none'    => __( 'None', 'ibx-wpfomo' ),
							'product' => __( 'Item Page', 'ibx-wpfomo' ),
							'custom'  => __( 'Custom URL', 'ibx-wpfomo' ),
						),
						'toggle'  => array(
							'custom' => array(
								'fields' => array( 'envato_custom_url' ),
							),
						),
					),
					'envato_custom_url' => array(
						'type'    => 'text',
						'label'   => __( 'Custom URL', 'ibx-wpfomo' ),
						'default' => '',
					),
				),
			),
		);

		return $fields;
	}

	/**
	 * Get lists from saved option.
	 *
	 * @since 2.1
	 * @param array  $field_data
	 * @param string $action
	 * @return array
	 */
	public function item_list_suggest_data( $field_data, $action ) {
		if ( 'get_envato_item_list' === $action ) {
			$field_data = $this->get_item_list();
		}
		return $field_data;
	}

	/**
	 * Add conversion content for envato.
	 *
	 * @since 2.1
	 * @param array  $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( $this->slug !== $settings->conversions_source ) {
			return $data;
		}

		$id = ! isset( $id ) ? $settings->post_id : $id;

		$data = IBX_WPFomo_Helper::get_cache_data( 'ibx_wpfomo_envato_sales_' . $id );

		if ( ! $data ) {
			$data = $this->get_envato_sales( $id );
		}

		return $data;
	}

	/**
	 * Connect Envato API.
	 *
	 * @since 2.1
	 * @return void
	 */
	public function connect_envato() {
		if ( ! isset( $_POST['token'] ) || empty( $_POST['token'] ) ) {
			wp_send_json_error( __( 'Error: You must provide an access token.', 'ibx-wpfomo' ) );
		}

		$token = trim( wp_unslash( $_POST['token'] ) );
		$connection = IBX_WPFomo_Envato_Helper::connect( $token );

		if ( isset( $connection['error'] ) && $connection['error'] ) {
			wp_send_json_error( $connection['error'] );
		} else {
			wp_send_json_success();
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 2.1
	 * @return object The IBX_WPFomo_Envato object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Envato ) ) {
			self::$instance = new IBX_WPFomo_Envato();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_envato = IBX_WPFomo_Envato::get_instance();
