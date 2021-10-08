<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for Freemius.
 *
 * @since 2.0.0
 */
class IBX_WPFomo_Freemius extends IBX_WPFomo_Addon {
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
		parent::__construct(
			array(
				'name' => 'Freemius',
				'slug' => 'freemius',
				'dir'  => IBX_WPFOMO_FREEMIUS_DIR,
				'url'  => IBX_WPFOMO_FREEMIUS_URL,
			)
		);

		require_once $this->dir . 'classes/class-ibx-wpfomo-freemius-helper.php';

		add_action( 'wp_ajax_ibx_wpfomo_connect_freemius', array( $this, 'connect_freemius' ) );
		add_action( 'ibx_wpfomo_admin_settings_scripts', array( $this, 'admin_settings_scripts' ) );
		//add_action( 'ibx_wpfomo_cron_update_data', array( $this, 'update_data' ), 10, 2 );
		add_action( 'ibx_wpfomo_save_post', array( $this, 'save_post' ), 10, 1 );
		add_filter( 'mbt_filter_suggest_field_data', array( $this, 'plugin_list_suggest_data' ), 10, 2 );
	}

	/**
	 * Enqueue scripts in admin.
	 *
	 * @since 1.0.0
	 */
	public function admin_settings_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-freemius-script', $this->url . 'assets/js/main.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
		wp_localize_script(
			'ibx-wpfomo-freemius-script',
			'wpfomo_freemius',
			array(
				'messages' => array(
					'connect_success' => __( 'Connected successfully!', 'ibx-wpfomo' ),
				),
			)
		);
	}


	/**
	 * Register admin settings.
	 *
	 * @since 1.0.0
	 * @param array $settings
	 * @return array
	 */
	public function register_admin_settings( $settings ) {
		$settings['sections']['freemius_api_key'] = array(
			'title'  => 'Freemius',
			'fields' => array(
				'freemius_store_id'   => array(
					'type'    => 'text',
					'label'   => __( 'Store ID', 'ibx-wpfomo' ),
					'default' => '',
					'help'    => __( 'Enter your Store ID. Your ID can be found in your Freemius account under My Store > Keys section.', 'ibx-wpfomo' ),
				),
				'freemius_public_key' => array(
					'type'     => 'text',
					'label'    => __( 'Public Key', 'ibx-wpfomo' ),
					'default'  => '',
					'help'     => __( 'Enter your Public key. Your ID can be found in your Freemius account under My Store > Keys section.', 'ibx-wpfomo' ),
					'sanitize' => false,
				),
				'freemius_secret_key' => array(
					'type'     => 'text',
					'label'    => __( 'Secret Key', 'ibx-wpfomo' ),
					'default'  => '',
					'help'     => __( 'Enter your Secret key and press "Connect" below. Your ID can be found in your Freemius account under My Store > Keys section.', 'ibx-wpfomo' ),
					'sanitize' => false,
				),
				'freemius_connect'    => array(
					'type'  => 'button',
					'label'	=> '&nbsp;',
					'text' => __( 'Connect', 'ibx-wpfomo' ),
					'class' => 'ibx-wpfomo-freemius-connect',
				),
			),
		);

		return $settings;
	}

	/**
	 * Conversion fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function fields() {
		$fields = array(
			'content' => array(
				'content_section' => array(
					'freemius_plugin'           => array(
						'type'        => 'suggest',
						'label'       => __( 'Show purchase of', 'ibx-wpfomo' ),
						'placeholder' => __( 'All Products', 'ibx-wpfomo' ),
						'default'     => '',
						'action'      => 'get_freemius_plugin_list',
						'options'     => array(
							'type' => 'freemius_plugin_list',
						),
					),
					'freemius_notification_msg' => array(
						'type'      => 'template',
						'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
						'default'   => array(
							'0' => __( '{{name}} recently purchased', 'ibx-wpfomo' ),
							'1' => '{{title}}',
							'2' => '{{time}}',
						),
						'variables' => array( '{{title}}', '{{name}}', '{{time}}', '{{city}}', '{{state}}', '{{country}}', '{{plan}}' ),
						'sanitize'  => false,
						'priority'  => 50,
					),
				),
			),
		);

		return $fields;
	}

	public function toggle_fields() {
		return array( 'product_img' );
	}

	/**
	 * Get lists from saved option.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_plugin_list() {
		$plugins = get_option( 'ibx_wpfomo_freemius_plugins' );
		$options = array();

		if ( is_array( $plugins ) && ! empty( $plugins ) ) {
			foreach ( $plugins as $plugin ) {
				$options[ $plugin->id ] = $plugin->title;
			}
		}

		return $options;
	}

	/**
	 * Get lists from saved option.
	 *
	 * @since 1.0.0
	 * @param array  $field_data
	 * @param string $action
	 * @return array
	 */
	public function plugin_list_suggest_data( $field_data, $action ) {
		if ( 'get_freemius_plugin_list' === $action ) {
			$field_data = self::get_plugin_list();
		}
		return $field_data;
	}

	/**
	 * Cron job to fetch mailchimp subscribers.
	 *
	 * @since 1.0.0
	 * @param int $post_id
	 * @return void
	 */
	public function save_post( $post_id ) {
		//IBX_WPFomo_Cron::set_cron( $post_id );
		$this->update_data( $post_id );
	}

	/**
	 * Cron job to fetch freemius payments.
	 *
	 * @since 1.0.0
	 * @param int $post_id
	 * @return void
	 */
	public function update_data( $post_id, $is_cron = false ) {

		if ( empty( $post_id ) ) {
			return;
		}
		$settings = MetaBox_Tabs::get_metabox_settings( $post_id );
		if ( 'conversion' !== $settings->type ) {
			return;
		}
		if ( 'freemius' == $settings->conversions_source ) {
			$this->get_freemius_payments( $post_id );
		}

	}

	/**
	 * Add conversion content for freemius.
	 *
	 * @since 1.0.0
	 * @param array  $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( $this->slug != $settings->conversions_source ) {
			return $data;
		}

		$transient_key = 'ibx_wpfomo_freemius_orders_' . $settings->post_id;

		$payments = IBX_WPFomo_Helper::get_cache_data( $transient_key );

		if ( empty( $payments ) ) {
			$payments = $this->get_freemius_payments( $settings->post_id );
		}

		$data = array(
			'fields' => $payments,
			'template' => $settings->freemius_notification_msg,
		);

		return $data;
	}

	/**
	 * Get saved payments from post meta.
	 *
	 * @since 1.0.0
	 * @param int $post_id
	 * @return array
	 */
	public function get_freemius_payments( $post_id ) {
		// $conversion_source = IBX_WPFomo_Admin::get_post_meta( $post_id, 'conversions_source' );

		// // Return if it's not freemius conversion.
		// if ( 'freemius' !== $conversion_source ) {
		// 	return $post_id;
		// }
		$freemius_plugin_ids = array();
		$freemius_plugin_ids = IBX_WPFomo_Admin::get_post_meta( $post_id, 'freemius_plugin' );

		$transient_key  = 'ibx_wpfomo_freemius_orders_' . $post_id;

		// Get limit.
		$limit = IBX_WPFomo_Admin::get_post_meta( $post_id, 'display_last' );

		// Set limit to 100 if empty.
		if ( empty( $limit ) || ! $limit ) {
			$limit = 100;
		}

		// Get display_last_days.
		$display_last_days = IBX_WPFomo_Admin::get_post_meta( $post_id, 'display_last_days' );

		// Set display_last_days to 100 if empty.
		if ( empty( $display_last_days ) || ! $display_last_days ) {
			$display_last_days = 100;
		}

		$now = strtotime( date( 'Y-m-d H:i:s' ) );

		// Get response from freemius.
		$response = IBX_WPFomo_Freemius_Helper::get_purchases_conversions( $freemius_plugin_ids, $limit, $post_id );

		// Manually filter data by days.
		$new_response = array_filter(
			$response,
			function ( $var ) use ( $display_last_days, $now ) {
				$created_dt = strtotime( $var['created'] );
				$datediff   = $now - $created_dt;
				$datediff   = round( $datediff / ( 60 * 60 * 24 ) );
				return ( $display_last_days >= $datediff );
			}
		);

		IBX_WPFomo_Helper::set_cache_data( $transient_key, $new_response );

		return $new_response;
	}

	/**
	 * Connect freemius via AJAX request.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function connect_freemius() {
		$response = array(
			'error' => false,
			'html'  => '',
		);

		if ( ! isset( $_POST['store_id'] ) || empty( $_POST['store_id'] ) ) {
			$response['error'] = __( 'Error: You must provide Store ID.', 'ibx-wpfomo' );
		}
		if ( ! isset( $_POST['public_key'] ) || empty( $_POST['public_key'] ) ) {
			$response['error'] = __( 'Error: You must provide Public Key.', 'ibx-wpfomo' );
		}
		if ( ! isset( $_POST['secret_key'] ) || empty( $_POST['secret_key'] ) ) {
			$response['error'] = __( 'Error: You must provide Secret Key.', 'ibx-wpfomo' );
		}

		if ( ! $response['error'] ) {
			$store_id   = wp_unslash( $_POST['store_id'] );
			$public_key = wp_unslash( $_POST['public_key'] );
			$secret_key = base64_decode( wp_unslash( $_POST['secret_key'] ) );

			$connection = IBX_WPFomo_Freemius_Helper::connect( $store_id, $public_key, $secret_key );
			//$response['con'] = json_encode($connection);
			if ( isset( $connection->error ) ) {
				// translators: %s is for error message.
				$response['error'] = sprintf( __( 'Error: %s', 'ibx-wpfomo' ), $connection->error->message );
			}
		}

		echo json_encode( $response );
		die();
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_Freemius object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Freemius ) ) {
			self::$instance = new IBX_WPFomo_Freemius();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_freemius = IBX_WPFomo_Freemius::get_instance();
