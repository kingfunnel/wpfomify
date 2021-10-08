<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_Analytics extends IBX_WPFomo_Addon {
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
			'name'  => 'Analytics',
			'slug'  => 'analytics',
			'dir'   => IBX_WPFOMO_ANALYTICS_DIR,
			'url'   => IBX_WPFOMO_ANALYTICS_URL,
		) );

		add_action( 'ibx_wpfomo_after_notification_markup', array( $this, 'render_analytics' ), 10, 3 );
		add_action( 'ibx_wpfomo_save_post', array( $this, 'clear_transients' ) );
	}

	public function have_conversions() {
		return false;
	}

	public function clear_transients( $post_id ) {
		delete_transient( '_ibx_wpfomo_page_count_' . $post_id );
		delete_transient( '_ibx_wpfomo_conversion_count_' . $post_id );
	}

	public function fields() {
		$fields = array(
			'configuration'	=> array(
				'page_analytics'       => array(
					'title'               => __( 'Page Analytics', 'ibx-wpfomo' ),
					'description'         => __( 'Displays random values from the given range as visitor count.', 'ibx-wpfomo' ),
					'collapsable'         => true,
					'toggle_type'         => 'field',
					'togggle_field_label' => array( 'Off', 'On' ),
					'fields'              => array(
						'page_analytics_min_value' => array(
							'type'     => 'number',
							'label'    => __( 'Min Value', 'ibx-wpfomo' ),
							'default'  => '100',
							'min'      => '1',
							'priority' => 120,
						),
						'page_analytics_max_value' => array(
							'type'     => 'number',
							'label'    => __( 'Max Value', 'ibx-wpfomo' ),
							'default'  => '300',
							'max'      => '300',
							'priority' => 130,
						),
						'page_analytics_title'     => array(
							'type'        => 'text',
							'label'       => __( 'Title', 'ibx-wpfomo' ),
							'placeholder' => __( 'People', 'ibx-wpfomo' ),
							'default'     => __( 'People', 'ibx-wpfomo' ),
							'required'    => true,
							'priority'    => 140,
						),
						'page_analytics_msg'       => array(
							'type'        => 'text',
							'label'       => __( 'Message', 'ibx-wpfomo' ),
							'placeholder' => __( 'are currently viewing this page.', 'ibx-wpfomo' ),
							'default'     => __( 'are currently viewing this page.', 'ibx-wpfomo' ),
							'required'    => true,
							'priority'    => 150,
						),
					),
				),
				'conversion_analytics' => array(
					'title'               => __( 'Conversion Analytics', 'ibx-wpfomo' ),
					'description'         => __( 'Displays random values from the given range as conversion count in last 24 hours.', 'ibx-wpfomo' ),
					'collapsable'         => true,
					'toggle_type'         => 'field',
					'togggle_field_label' => array( 'Off', 'On' ),
					'fields'              => array(
						'conversion_analytics_source'	=> array(
							'type'		=> 'select',
							'label'		=> __( 'Source', 'ibx-wpfomo' ),
							'default'	=> 'random',
							'options'	=> apply_filters( 'ibx_wpfomo_conversion_analytics_source', array(
								'custom'	=> __( 'Custom', 'ibx-wpfomo' ),
							) ),
							'toggle'	=> array(
								'custom'	=> array(
									'fields'	=> array( 'conversion_analytics_min_value', 'conversion_analytics_max_value' ),
								),
							),
						),
						'conversion_analytics_min_value' => array(
							'type'     => 'number',
							'label'    => __( 'Min Value', 'ibx-wpfomo' ),
							'default'  => '100',
							'min'      => '1',
							'priority' => 110,
						),
						'conversion_analytics_max_value' => array(
							'type'     => 'number',
							'label'    => __( 'Max Value', 'ibx-wpfomo' ),
							'default'  => '300',
							'max'      => '300',
							'priority' => 120,
						),
						'conversion_analytics_title' => array(
							'type'        => 'text',
							'label'       => __( 'Title', 'ibx-wpfomo' ),
							'placeholder' => __( 'Conversions', 'ibx-wpfomo' ),
							'default'     => __( 'Conversions', 'ibx-wpfomo' ),
							'required'    => true,
							'priority'    => 140,
						),
						'conversion_analytics_msg' => array(
							'type'        => 'text',
							'label'       => __( 'Message', 'ibx-wpfomo' ),
							'placeholder' => __( 'in last 24 hours.', 'ibx-wpfomo' ),
							'default'     => __( 'in last 24 hours.', 'ibx-wpfomo' ),
							'required'    => true,
							'priority'    => 150,
						),
					),
				),
			),
		);

		return $fields;
	}

	private function display_page_analytics( $settings ) {
		if ( isset( $settings->mbt_section_toggle ) && is_array( $settings->mbt_section_toggle ) ) {
			if ( isset( $settings->mbt_section_toggle['page_analytics'] ) && $settings->mbt_section_toggle['page_analytics'] ) {
				return true;
			}
		}

		return false;
	}

	private function display_conversion_analytics( $settings ) {
		if ( isset( $settings->mbt_section_toggle ) && is_array( $settings->mbt_section_toggle ) ) {
			if ( isset( $settings->mbt_section_toggle['conversion_analytics'] ) && $settings->mbt_section_toggle['conversion_analytics'] ) {
				return true;
			}
		}

		return false;
	}

	public function render_analytics( $settings, $classes, $sequence ) {
		$id = isset( $id ) ? $id : $settings->post_id;

		$display_page_analytics = $this->display_page_analytics( $settings );
		$display_conversion_analytics = $this->display_conversion_analytics( $settings );

		// store data in transient for the given time in options.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		if ( $display_page_analytics ) {
			$page_analytics_min_value = isset( $settings->page_analytics_min_value ) ? absint( $settings->page_analytics_min_value ) : 0;
			$page_analytics_max_value = isset( $settings->page_analytics_max_value ) ? absint( $settings->page_analytics_max_value ) : 0;

			$visitor_count = get_transient( '_ibx_wpfomo_page_count_' . $id );
			if ( false === $visitor_count ) {
				$visitor_count = random_int( $page_analytics_min_value, $page_analytics_max_value );
				set_transient( '_ibx_wpfomo_page_count_' . $id, $visitor_count, ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
			}
		}

		if ( $display_conversion_analytics ) {
			$conversion_analytics_min_value = isset( $settings->conversion_analytics_min_value ) ? absint( $settings->conversion_analytics_min_value ) : 0;
			$conversion_analytics_max_value = isset( $settings->conversion_analytics_max_value ) ? absint( $settings->conversion_analytics_max_value ) : 0;

			// Get an existing copy of our transient data.
			$conversion_count = get_transient( '_ibx_wpfomo_conversion_count_' . $id );
			if ( false === $conversion_count ) {
				// It wasn't there, so regenerate the data and save the transient.
				$conversion_count = random_int( $conversion_analytics_min_value, $conversion_analytics_max_value );
				$conversion_count = apply_filters( 'ibx_wpfomo_conversion_count', $conversion_count, $settings );
				set_transient( '_ibx_wpfomo_conversion_count_' . $id, $conversion_count, ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
			}
		}

		// render page analytics.
		if ( isset( $visitor_count ) ) {
			$analytics_title = $settings->page_analytics_title;
			$analytics_text  = $settings->page_analytics_msg;
			$analytics_count = $visitor_count;
			$analytics_type  = 'page';
			if ( $display_page_analytics && $analytics_count > 0 ) {
				include IBX_WPFOMO_ANALYTICS_DIR . 'includes/frontend-notification-analytics.php';
				$sequence ++;
			}
		}

		// render conversion analytics.
		if ( isset( $conversion_count ) ) {
			$analytics_title = $settings->conversion_analytics_title;
			$analytics_text  = $settings->conversion_analytics_msg;
			$analytics_count = $conversion_count;
			$analytics_type  = 'conversion';
			if ( $display_conversion_analytics && $analytics_count > 0 ) {
				include IBX_WPFOMO_ANALYTICS_DIR . 'includes/frontend-notification-analytics.php';
				$sequence ++;
			}
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_Analytics object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Analytics ) ) {
			self::$instance = new IBX_WPFomo_Analytics();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_analytics = IBX_WPFomo_Analytics::get_instance();
