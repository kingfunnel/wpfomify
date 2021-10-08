<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_User_Registration extends IBX_WPFomo_Addon {
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
			'name'  => __( 'User Registration', 'ibx-wpfomo' ),
			'slug'  => 'user-registration',
			'dir'   => IBX_WPFOMO_USER_REGISTRATION_DIR,
			'url'   => IBX_WPFOMO_USER_REGISTRATION_URL,
		) );

		//require_once $this->dir . 'classes/class-ibx-wpfomo-user-registration-helper.php';

		add_action( 'admin_notices', array( $this, 'render_notices' ) );
		add_action( 'ibx_wpfomo_save_post', array( $this, 'cache_data' ) );
	}

	/**
	 * Enqueue scripts in admin.
	 *
	 * @since 2.1
	 */
	public function admin_enqueue_scripts() {
		//wp_enqueue_script( 'ibx-wpfomo-user-reg', $this->url . 'assets/js/meta.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
	}

	/**
	 * Render any notices come up.
	 *
	 * @since 2.1
	 */
	public function render_notices() {
		echo IBX_WPFomo_Helper::get_notice();
	}

	/**
	 * Cache data for notification.
	 *
	 * @since 2.1
	 * @param int $post_id
	 */
	public function cache_data( $post_id ) {
		$settings = MetaBox_Tabs::get_metabox_settings( $post_id );

		if ( 'conversion' !== $settings->type ) {
			return;
		}

		if ( $this->slug !== $settings->conversions_source ) {
			return;
		}

		if ( empty( $settings->user_reg_role ) ) {
			IBX_WPFomo_Helper::set_notice(
				__( 'You must select a user role to setup notifications.', 'ibx-wpfomo' ),
				'error'
			);
			return;
		}

		$url = $settings->user_reg_notification_link;

		$fields = array();

		$users = $this->get_users( $settings );

		if ( empty( $users ) ) {
			$error = sprintf( __( 'No users found with the role: %s', 'ibx-wpfomo' ), '<strong>' . $this->get_user_roles( $settings->user_reg_role ) . '</strong>' );
			$error .= ' ' . __( 'or probably you have entered too few days in Behaviour settings under Customize tab.', 'ibx-wpfomo' );
			IBX_WPFomo_Helper::set_notice(
				$error,
				'error'
			);
			return;
		}
		if ( is_wp_error( $users ) ) {
			IBX_WPFomo_Helper::set_notice(
				$users->get_error_message(),
				'error'
			);
			return;
		}

		foreach ( $users as $user ) {
			$data 		= get_userdata( $user->ID );
			$email 		= $user->user_email;
			$first_name = $data->first_name;
			$last_name 	= $data->last_name;
			$name 		= '';

			if ( empty( $first_name ) ) {
				$name = IBX_WPFomo_Helper::get_someone_translation();
			} else {
				$name = $first_name;
				if ( ! empty( $last_name ) ) {
					$name .= ' ' . $last_name[0] . '.';
				}
			}

			$time = $user->user_registered;
			$time = IBX_WPFomo_Helper::get_timeago_html( $time );

			$fields[] = array(
				'name'	=> $name,
				'email'	=> $email,
				'time'	=> $time,
				'url'	=> $url,
				'title'	=> ''
			);
		}

		$data = array(
			'fields'	=> $fields,
			'template'	=> $settings->user_reg_notification_tmpl
		);

		// store data in transient for the given time in plugin options.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		delete_transient( 'ibx_wpfomo_user_reg_data_' . $post_id );
		set_transient( 'ibx_wpfomo_user_reg_data_' . $post_id, maybe_serialize( $data ), ( $cache_duration / 60 ) * HOUR_IN_SECONDS );

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
					'user_reg_role'	=> array(
						'type'		=> 'select',
						'label'		=> __( 'Select User Role', 'ibx-wpfomo' ),
						'default'	=> '',
						'options'	=> $this->get_user_roles(),
					),
					'user_reg_notification_tmpl' => array(
						'type'      => 'template',
						'label'     => __( 'Notification Template', 'ibx-wpfomo' ),
						'default'   => array(
							'0' => '{{name}} recently signed up for',
							'1' => __( 'The Event', 'ibx-wpfomo' ),
							'2' => '{{time}}',
						),
						'variables' => array( '{{name}}', '{{time}}' ),
						'sanitize'  => false,
						'hide_if'	=> '!conversion',
					),
					'user_reg_notification_link' => array(
						'type'    => 'text',
						'label'   => __( 'Link Notification to', 'ibx-wpfomo' ),
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
	 * Get data from transient.
	 *
	 * @since 2.1
	 * @param object $settings	Post's metadata.
	 * @return array
	 */
	private function get_cached_data( $settings ) {
		$id = isset( $id ) ? $id : $settings->post_id;

		// transient key.
		$transient_key = 'ibx_wpfomo_user_reg_data_' . $id;

		// get data from transient and unserialize it.
		$cached_data = maybe_unserialize( get_transient( $transient_key ) );

		// return data if exist in transient.
		if ( is_array( $cached_data ) && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		return $this->cache_data( $id );
	}

	private function get_user_roles( $label_for = '' ) {
		global $wp_roles;

		$_wp_roles = $wp_roles;

		if ( ! isset( $wp_roles ) || empty( $_wp_roles ) ) {
			$_wp_roles = get_editable_roles();
		}

		$roles      = isset( $_wp_roles->roles ) ? $_wp_roles->roles : array();
		$user_roles = array(
			''	=> __( '-- Select --', 'ibx-wpfomo' ),
		);

		foreach ( $roles as $role_key => $role ) {
			$user_roles[ $role_key ] = $role['name'];
		}

		if ( ! empty( $label_for ) && isset( $user_roles[ $label_for ] ) ) {
			return $user_roles[ $label_for ];
		}

		return $user_roles;
	}

	private function get_users( $settings ) {
		$role = $settings->user_reg_role;
		$args = array(
			'role'		=> $role,
			'orderby' 	=> 'user_registered',
			'order' 	=> 'DESC',
		);

		if ( isset( $settings->display_last_days ) ) {
			// get time settings for entries to be fetched.
			$entries_days 	= empty( $settings->display_last_days ) ? 3 : $settings->display_last_days;
			// convert time into seconds.
			$entries_time 	= $entries_days * 24 * 60 * 60;
			// get current time.
			$current_time 	= time();
			// get timezone offset.
			$gmt_offset		= get_option( 'gmt_offset' );
			// set start time.
			$start_time 	= date( 'Y-m-d', ($current_time - $entries_time) + ($gmt_offset * HOUR_IN_SECONDS * 2) );
			// set end time.
			$end_time 		= date( 'Y-m-d H:i:s', $current_time + ($gmt_offset * HOUR_IN_SECONDS * 2) );
			
			$args['date_query'] = array(
				array(
					'after'	=> $start_time,
					'before' => $end_time,
					'inclusive' => true
				)
			);
		}

		if ( ! empty( $settings->display_last ) ) {
			$args['number'] = $settings->display_last;
		}

		$users = get_users( $args );

		return $users;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_User_Registration object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_User_Registration ) ) {
			self::$instance = new IBX_WPFomo_User_Registration();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_user_reg = IBX_WPFomo_User_Registration::get_instance();
