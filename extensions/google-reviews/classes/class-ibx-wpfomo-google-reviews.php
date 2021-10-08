<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_Google_Reviews extends IBX_WPFomo_Addon {
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
			'name'  => 'Google Reviews',
			'slug'  => 'google-reviews',
			'dir'   => IBX_WPFOMO_GOOGLE_REVIEWS_DIR,
			'url'   => IBX_WPFOMO_GOOGLE_REVIEWS_URL,
		) );

		require_once $this->dir . 'classes/class-ibx-wpfomo-google-reviews-helper.php';

		add_action( 'ibx_wpfomo_admin_settings_scripts', array( $this, 'admin_settings_scripts' ) );
		add_action( 'wp_ajax_ibx_wpfomo_connect_google_places_api', array( $this, 'connect_api' ) );
		add_action( 'admin_notices', array( $this, 'render_admin_notices' ) );
		add_action( 'ibx_wpfomo_save_post', array( $this, 'cache_data' ) );
	}

	/**
	 * A flag to determine whether the extension is for conversions.
	 *
	 * @since 2.1
	 */
	public function have_conversions() {
		return false;
	}

	/**
	 * A flag to determine whether the extension is for reviews.
	 *
	 * @since 2.1
	 */
	public function have_reviews() {
		return true;
	}

	/**
	 * Enqueue scripts in admin.
	 *
	 * @since 2.1
	 */
	public function admin_settings_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-google-reviews-script', $this->url . 'assets/js/admin.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
		wp_localize_script(
			'ibx-wpfomo-google-reviews-script',
			'wpfomo_google_reviews',
			array(
				'messages' => array(
					'connect_success' => __( 'Connected successfully!', 'ibx-wpfomo' ),
				),
			)
		);
	}

	/**
	 * Enqueue scripts in post editor.
	 *
	 * @since 2.1
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-google-reviews-meta', $this->url . 'assets/js/meta.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
	}

	/**
	 * Connect Google Places API.
	 *
	 * @since 2.1
	 */
	public function connect_api() {
		if ( ! isset( $_POST['api_key'] ) || empty( $_POST['api_key'] ) ) {
			wp_send_json_error( __( 'Error: You must provide Google Places API Key.', 'ibx-wpfomo' ) );
		}

		$api_key = esc_attr( wp_unslash( $_POST['api_key'] ) );

		$response = IBX_WPFomo_Google_Reviews_Helper::check_api( $api_key );

		if ( 'success' === $response ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( $response );
		}
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
	 * Cache API response and save data to transient.
	 *
	 * @since 2.1
	 */
	public function cache_data( $post_id ) {
		$settings = MetaBox_Tabs::get_metabox_settings( $post_id );

		if ( 'reviews' !== $settings->type ) {
			return;
		}

		if ( $this->slug != $settings->reviews_source ) {
			return;
		}

		$api_key = IBX_WPFomo_Admin::get_settings( 'google_places_api_key' );
		$place_id = $settings->gr_place_id;

		if ( empty( $api_key ) ) {
			IBX_WPFomo_Helper::set_notice( __( 'Error: You must provide Google Place API Key. Go to WPfomify settings and add API key.', 'ibx-wpfomo' ), 'error' );
			return;
		}

		if ( empty( $place_id ) ) {
			IBX_WPFomo_Helper::set_notice( __( 'Error: You must provide Google Place ID.', 'ibx-wpfomo' ), 'error' );
			return;
		}

		$place_data = IBX_WPFomo_Google_Reviews_Helper::get_place_data( $api_key, $place_id );

		if ( $place_data['error'] ) {
			IBX_WPFomo_Helper::set_notice( $place_data['error'], 'error' );
			return;
		}

		if ( empty( $place_data['data'] ) || empty( $place_data['data']['reviews'] ) ) {
			IBX_WPFomo_Helper::set_notice( __( 'This place does not have reviews yet.', 'ibx-wpfomo' ), 'error' );
			return;
		}

		$fields = array();
		$reviews_data = array();
		$reviews = IBX_WPFomo_Google_Reviews_Helper::parse_reviews( $place_data['data']['reviews'] );
		$count = 1;

		// Notification link.
		if ( 'map' === $settings->gr_notification_link ) {
			if ( isset( $place_data['data']['map'] ) && ! empty( $place_data['data']['map'] ) ) {
				$fields['url'] = $place_data['data']['map'];
			}
		} elseif ( 'custom' === $settings->gr_notification_link ) {
			if ( ! empty( $settings->gr_notification_link_custom ) ) {
				$fields['url'] = $settings->gr_notification_link_custom;
			}
		}

		foreach ( $reviews as $time => $review ) {
			if ( isset( $settings->gr_reviews_condition ) && ! empty( $settings->gr_reviews_condition ) ) {
				if ( $review['rating'] <= (float) $settings->gr_reviews_condition ) {
					continue;
				}
			}
			if ( ! empty( $settings->gr_number_of_reviews ) && absint( $settings->gr_number_of_reviews ) < $count ) {
				break;
			}
			$fields['title'] = $place_data['data']['name'];
			$fields['name'] = $review['author_name'];
			$fields['image']['url'] = $review['author_image'];
			$fields['rating'] = $review['rating'];
			$fields['time'] = $review['relative_time'];
			$fields['review'] = $review['text'];

			$reviews_data[] = $fields;

			$count++;
		}

		$data = array(
			'fields'	=> ! empty( $reviews_data ) ? $reviews_data : array( $fields ),
			'template'	=> $settings->gr_notification_tmpl,
		);

		IBX_WPFomo_Helper::set_cache_data( 'ibx_wpfomo_google_reviews_' . $post_id, $data );

		return $data;
	}

	/**
	 * Register admin settings fields.
	 *
	 * @since 2.1
	 * @param array $settings
	 * @return array
	 */
	public function register_admin_settings( $settings ) {
		$settings['sections']['google_api'] = array(
			'title'  => 'Google API',
			'fields' => array(
				'google_places_api_key'   => array(
					'type'    => 'text',
					'label'   => __( 'Google Places API Key', 'ibx-wpfomo' ),
					'default' => '',
					'help'    => __( 'To get your Google Places API key, <a href="https://wpfomify.com/docs/sections/integrations/display-business-reviews-google/" target="_blank">click here</a>', 'ibx-wpfomo' ),
				),
				'google_places_api_connect'    => array(
					'type'  => 'button',
					'label'	=> '&nbsp;',
					'text' => __( 'Connect', 'ibx-wpfomo' ),
					'class' => 'ibx-wpfomo-google-places-api-connect',
				),
			),
		);

		return $settings;
	}

	/**
	 * Register reviews fields.
	 *
	 * @since 2.1
	 */
	public function fields() {
		$fields = array(
			'content'	=> array(
				'content_section'	=> array(
					'gr_place_id'	=> array(
						'type'			=> 'text',
						'label'			=> __( 'Place ID', 'ibx-wpfomo' ),
						'default'		=> '',
						'help' => __( '<a href="https://developers.google.com/places/place-id" target="_blank" rel="noopener"><b>Click here</b></a> to find your Google Place ID.', 'ibx-wpfomo' ),
					),
					'gr_notification_tmpl' => array(
						'type'      => 'template',
						'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
						'default'   => array(
							'0' => '{{title}}',
							'1' => '{{rating}}',
							'2' => __( 'by {{name}}', 'ibx-wpfomo' ),
						),
						'variables' => array( '{{name}}', '{{title}}', '{{rating}}', '{{review}}', '{{time}}' ),
						'sanitize'  => false,
						'hide_if'	=> '!reviews',
					),
					'gr_number_of_reviews'	=> array(
						'type'		=> 'number',
						'label'		=> __( 'Number of Reviews', 'ibx-wpfomo' ),
						'default'	=> '',
						'help'		=> __( 'Leave empty to get all reviews.', 'ibx-wpfomo' ),
						'hide_if'	=> '!reviews',
					),
					'gr_reviews_condition'	=> array(
						'type'		=> 'number',
						'label'		=> __( 'Minimum Rating', 'ibx-wpfomo' ),
						'help'		=> __( 'Show notification if rating is greater than the given value.', 'ibx-wpfomo' ),
						'default'	=> 3,
						'hide_if'	=> '!reviews',
					),
					'gr_notification_link'	=> array(
						'type'		=> 'select',
						'label'		=> __( 'Link Notification to', 'ibx-wpfomo' ),
						'default'	=> 'none',
						'options'	=> array(
							'none'		=> __( 'None', 'ibx-wpfomo' ),
							'map'		=> __( 'Location Map', 'ibx-wpfomo' ),
							'custom'	=> __( 'Custom', 'ibx-wpfomo' ),
						),
						'toggle'	=> array(
							'custom'	=> array(
								'fields'	=> array( 'gr_notification_link_custom' ),
							),
						),
					),
					'gr_notification_link_custom' => array(
						'type'    => 'text',
						'label'   => __( 'Custom Link', 'ibx-wpfomo' ),
						'default' => '',
					),
				),
			),
		);

		return $fields;
	}

	/**
	 * Add reviews data for notification.
	 *
	 * @since 2.1
	 * @param array $data		Reviews field.
	 * @param object $settings	Post's metadata.
	 * @return array
	 */
	public function add_reviews_data( $data, $settings ) {
		if ( $this->slug != $settings->reviews_source ) {
			return $data;
		}

		$id = ! isset( $id ) ? $settings->post_id : $id;

		$data = IBX_WPFomo_Helper::get_cache_data( 'ibx_wpfomo_google_reviews_' . $id );

		if ( ! $data ) {
			$data = $this->cache_data( $id );
		}

		return $data;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_Google_Reviews object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Google_Reviews ) ) {
			self::$instance = new IBX_WPFomo_Google_Reviews();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_google_reviews = IBX_WPFomo_Google_Reviews::get_instance();
