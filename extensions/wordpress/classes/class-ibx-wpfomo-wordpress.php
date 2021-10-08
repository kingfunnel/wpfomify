<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_WordPress extends IBX_WPFomo_Addon {
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
		parent::__construct( array(
			'name'  => 'WordPress',
			'slug'  => 'wordpress',
			'dir'   => IBX_WPFOMO_WORDPRESS_DIR,
			'url'   => IBX_WPFOMO_WORDPRESS_URL,
		) );

		require_once $this->dir . 'classes/class-ibx-wpfomo-wordpress-reviews.php';
		require_once $this->dir . 'classes/class-ibx-wpfomo-wordpress-helper.php';

		add_action( 'admin_notices', array( $this, 'render_notices' ) );
		add_action( 'ibx_wpfomo_save_post', array( $this, 'cache_data' ) );
	}

	/**
	 * Enqueue scripts in admin.
	 *
	 * @since 2.1
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-wprepo', $this->url . 'assets/js/meta.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
	}

	/**
	 * Condition to enable reviews.
	 *
	 * @since 2.1
	 */
	public function have_reviews() {
		return true;
	}

	/**
	 * Render any notices come up.
	 *
	 * @since 2.1
	 */
	public function render_notices() {
		echo IBX_WPFomo_WordPress_Helper::get_notice();
	}

	/**
	 * Cache data for notification.
	 *
	 * @since 2.1
	 * @param int $post_id
	 */
	public function cache_data( $post_id ) {
		$settings = MetaBox_Tabs::get_metabox_settings( $post_id );

		if ( 'conversion' === $settings->type && $this->slug != $settings->conversions_source ) {
			return;
		}
		if ( 'reviews' === $settings->type && $this->slug != $settings->reviews_source ) {
			return;
		}

		$repo_info = array();
		$fields = array();
		$reviews_data = array();

		// Get plugin data.
		if ( 'plugin' === $settings->wp_repo_type && ! empty( $settings->wp_repo_slug_plugin ) ) {
			$repo_info = IBX_WPFomo_WordPress_Helper::get_plugin_info( $settings->wp_repo_slug_plugin );

			if ( empty( $repo_info ) || ! is_array( $repo_info ) ) {
				return;
			}

			// Check for icon if available in repo data.
			if ( isset( $repo_info['icons'] ) && is_array( $repo_info['icons'] ) ) {
				if ( isset( $repo_info['icons']['2x'] ) ) {
					$fields['image'] = array(
						'url'	=> $repo_info['icons']['2x'],
					);
				}
			}
		}

		// Get theme data.
		if ( 'theme' === $settings->wp_repo_type && ! empty( $settings->wp_repo_slug_theme ) ) {
			$repo_info = IBX_WPFomo_WordPress_Helper::get_theme_info( $settings->wp_repo_slug_theme );

			if ( empty( $repo_info ) || ! is_array( $repo_info ) ) {
				return;
			}

			// Check for screenshot URL if available in repo data.
			if ( isset( $repo_info['screenshot_url'] ) && ! empty( $repo_info['screenshot_url'] ) ) {
				$fields['image'] = array(
					'url'	=> $repo_info['screenshot_url'],
				);
			}
		}

		// Plugin/Theme name.
		$fields['title'] = $repo_info['name'];

		// Plugin/Theme downloads count.
		if ( isset( $repo_info['downloads'] ) ) {
			foreach ( $repo_info['downloads'] as $time_key => $count ) {
				$fields[ 'count_' . $time_key ] = IBX_WPfomo_WordPress_Helper::format_number( $count );
			}
		}

		// Plugin/Theme active installs count.
		if ( isset( $repo_info['active_installs'] ) ) {
			$fields['count_active_installs'] = $repo_info['active_installs'];
		}

		// Plugin/Theme version.
		if ( isset( $repo_info['version'] ) ) {
			$fields['version'] = $repo_info['version'];
		}

		// Plugin/Theme link.
		if ( 'repo' === $settings->wp_notification_link ) {
			$fields['url'] = $repo_info['link'];
		} elseif ( 'custom' === $settings->wp_notification_link ) {
			if ( ! empty( $settings->wp_notification_link_custom ) ) {
				$fields['url'] = $settings->wp_notification_link_custom;
			}
		}

		// Plugin reviews.
		if ( 'reviews' === $settings->type ) {
			if ( isset( $repo_info['reviews'] ) && ! empty( $repo_info['reviews'] ) ) {
				foreach ( $repo_info['reviews'] as $review ) {
					if ( ! empty( $settings->reviews_condition ) ) {
						if ( $review['rating'] < floatval( $settings->reviews_condition ) ) {
							continue;
						}
					}
					$fields['name'] = $review['username']['text'];
					// $fields['image'] = array(
					// 	'url' => $review['avatar']['src'],
					// );
					$fields['rating'] = $review['rating'];
					$fields['review_title'] = $review['title'];
					$fields['date'] = $review['date'];

					$reviews_data[] = $fields;
				}
			}
		}

		$data = array(
			'fields'	=> 'reviews' === $settings->type ? $reviews_data : array( $fields ),
			'template'	=> 'reviews' === $settings->type ? $settings->wp_notification_reviews_tmpl : $settings->wp_notification_tmpl,
		);

		// store data in transient for the given time in plugin options.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		delete_transient( 'ibx_wpfomo_wprepo_data_' . $post_id );
		set_transient( 'ibx_wpfomo_wprepo_data_' . $post_id, maybe_serialize( $data ), ( $cache_duration / 60 ) * HOUR_IN_SECONDS );

		return $data;
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
					'wp_repo_type'	=> array(
						'type'		=> 'select',
						'label'		=> __( 'Repository Type', 'ibx-wpfomo' ),
						'default'	=> 'plugin',
						'options'	=> array(
							'plugin'	=> __( 'Plugin', 'ibx-wpfomo' ),
							'theme'		=> __( 'Theme', 'ibx-wpfomo' ),
						),
						'toggle'	=> array(
							'plugin'	=> array(
								'fields'	=> array( 'wp_repo_slug_plugin' ),
							),
							'theme'	=> array(
								'fields'	=> array( 'wp_repo_slug_theme' ),
							),
						),
					),
					'wp_repo_slug_plugin'	=> array(
						'type'			=> 'text',
						'label'			=> __( 'Plugin Slug', 'ibx-wpfomo' ),
						'default'		=> '',
					),
					'wp_repo_slug_theme'	=> array(
						'type'			=> 'text',
						'label'			=> __( 'Theme Slug', 'ibx-wpfomo' ),
						'default'		=> '',
					),
					'wp_notification_tmpl' => array(
						'type'      => 'template',
						'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
						'default'   => array(
							'0' => '{{title}}',
							'1' => __( 'has been downloaded {{count_today}} times today', 'ibx-wpfomo' ),
							'2' => '',
						),
						'variables' => array( '{{title}}', '{{version}}', '{{count_today}}', '{{count_yesterday}}', '{{count_last_week}}', '{{count_all_time}}', '{{count_active_installs}}' ),
						'sanitize'  => false,
						'hide_if'	=> '!conversion',
					),
					'wp_notification_reviews_tmpl' => array(
						'type'      => 'template',
						'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
						'default'   => array(
							'0' => __( '{{name}} reviewed', 'ibx-wpfomo' ),
							'1' => '{{title}}',
							'2' => '{{rating}}',
						),
						'variables' => array( '{{title}}', '{{version}}', '{{name}}', '{{rating}}', '{{review_title}}', '{{date}}' ),
						'sanitize'  => false,
						'hide_if'	=> '!reviews',
					),
					'wp_notification_reviews_condition'	=> array(
						'type'		=> 'number',
						'label'		=> __( 'Minimum Rating', 'ibx-wpfomo' ),
						'help'		=> __( 'Show notification if rating is greater than the given value.', 'ibx-wpfomo' ),
						'default'	=> 3,
						'hide_if'	=> '!reviews',
					),
					'wp_notification_link'	=> array(
						'type'		=> 'select',
						'label'		=> __( 'Link Notification to', 'ibx-wpfomo' ),
						'default'	=> 'none',
						'options'	=> array(
							'none'		=> __( 'None', 'ibx-wpfomo' ),
							'repo'		=> __( 'Repository', 'ibx-wpfomo' ),
							'custom'	=> __( 'Custom', 'ibx-wpfomo' ),
						),
						'toggle'	=> array(
							'custom'	=> array(
								'fields'	=> array( 'wp_notification_link_custom' ),
							),
						),
					),
					'wp_notification_link_custom' => array(
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
	 * Add conversion data for notification.
	 *
	 * @since 2.1
	 * @param array $data		Conversion field.
	 * @param object $settings	Post's metadata.
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( $this->slug != $settings->conversions_source ) {
			return $data;
		}

		$cached_data = $this->get_cached_data( $settings );

		if ( is_array( $cached_data ) && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		return $data;
	}

	/**
	 * Add reviews data for notification.
	 *
	 * @since 2.1
	 * @param array $data		Conversion field.
	 * @param object $settings	Post's metadata.
	 * @return array
	 */
	public function add_reviews_data( $data, $settings ) {
		if ( $this->slug != $settings->reviews_source ) {
			return $data;
		}

		$cached_data = $this->get_cached_data( $settings );

		if ( is_array( $cached_data ) && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		return $data;
	}

	/**
	 * Get data from transient.
	 *
	 * @since 2.1
	 * @param object $settings	Post's metadata.
	 * @return array
	 */
	private function get_cached_data( $settings ) {
		if ( ! isset( $settings->wp_repo_type ) ) {
			return false;
		}

		if ( 'plugin' === $settings->wp_repo_type && empty( $settings->wp_repo_slug_plugin ) ) {
			return false;
		}

		if ( 'theme' === $settings->wp_repo_type && empty( $settings->wp_repo_slug_theme ) ) {
			return false;
		}

		$id = isset( $id ) ? $id : $settings->post_id;

		// transient key.
		$transient_key = 'ibx_wpfomo_wprepo_data_' . $id;

		// get data from transient and unserialize it.
		$cached_data = maybe_unserialize( get_transient( $transient_key ) );

		// return data if exist in transient.
		if ( is_array( $cached_data ) && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		return $this->cache_data( $id );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_WordPress object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_WordPress ) ) {
			self::$instance = new IBX_WPFomo_WordPress();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_wordpress = IBX_WPFomo_WordPress::get_instance();
