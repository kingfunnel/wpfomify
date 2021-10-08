<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'IBX_WPFomo_Admin' ) ) {
	/**
	 * Handles logic for admin settings and post types.
	 *
	 * @since 1.0.0
	 */
	final class IBX_WPFomo_Admin {

		/**
		 * Holds post type slug.
		 *
		 * @since 1.0.0
		 * @var string $type
		 */
		static public $type = 'ibx_wpfomo';

		/**
		 * Holds settings.
		 *
		 * @since 1.0.0
		 * @var array $settings
		 */
		static public $settings = array();

		/**
		 * Holds error messages.
		 *
		 * @since 1.0.0
		 * @var array $errors
		 */
		static public $errors = array();

		/**
		 * Initialize class.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function init() {
			add_action( 'plugins_loaded', __CLASS__ . '::init_hooks' );

			// Activation
			register_activation_hook( IBX_WPFOMO_FILE, __CLASS__ . '::activate' );

			add_action( 'admin_init', __CLASS__ . '::redirect' );
		}

		/**
		 * Initialize hooks and filters.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function init_hooks() {
			if ( ! class_exists( 'MetaBox_Tabs' ) ) {
				require_once IBX_WPFOMO_DIR . 'includes/mbt/metabox-tabs.php';
			}

			add_action( 'init',                     __CLASS__ . '::register_post_type' );
			add_action( 'init',                     __CLASS__ . '::register_settings_fields' );
			add_action( 'add_meta_boxes',           __CLASS__ . '::add_tracking_metabox' );
			add_action( 'admin_enqueue_scripts',    __CLASS__ . '::load_scripts' );
			add_action( 'admin_head',               __CLASS__ . '::admin_style' );
			add_action( 'admin_menu',               __CLASS__ . '::admin_settings_menu' );
			add_action( 'admin_footer',             __CLASS__ . '::preview' );
			add_action( 'admin_footer',             __CLASS__ . '::render_settings_js' );
			add_action( 'admin_notices',            __CLASS__ . '::admin_notices' );
			add_action( 'network_admin_notices',    __CLASS__ . '::admin_notices' );

			add_filter( 'manage_' . self::$type . '_posts_columns', 		__CLASS__ . '::add_post_custom_column' );
			add_action( 'manage_' . self::$type . '_posts_custom_column', 	__CLASS__ . '::manage_post_custom_column', 10, 2 );

			add_filter( 'mbt_metabox_fields',		__CLASS__ . '::set_tracking_fields', 10, 1 );
			add_action( 'mbt_update_post', 			__CLASS__ . '::save_post', 10, 4 );
			add_action( 'mbt_metabox_tabs_after_content_wrap', __CLASS__ . '::render_metabox_footer', 10, 2 );
			add_filter( 'cron_schedules', 			__CLASS__ . '::custom_cache_duration' );
		}

		/**
		* Called on plugin activation.
		*
		* @since 2.0
		* @return void
		*/
		static public function activate() {
			self::trigger_activate_notice();

			flush_rewrite_rules();
		}

		/**
		* Sets the transient that triggers the activation notice
		* or setting page redirect.
		*
		* @since 2.0
		* @return void
		*/
		static public function trigger_activate_notice() {
			if ( current_user_can( 'delete_users' ) ) {
				set_transient( '_ibx_wpfomo_activation_admin_notice', true, 30 );
			}
		}

		/**
		 * Redirects to the setting page.
		 *
		 * @since 2.0
		 * @return void
		 */
		static public function redirect() {
			// Bail if no activation transient is set.
			if ( ! get_transient( '_ibx_wpfomo_activation_admin_notice' ) ) {
				return;
			}

			// Delete the activation transient.
			delete_transient( '_ibx_wpfomo_activation_admin_notice' );

			if ( ! is_multisite() ) {
				// Redirect to the welcome page.
				wp_safe_redirect( add_query_arg( array(
					'post_type' => 'ibx_wpfomo',
					'page'		=> 'wpfomo-settings',
				), admin_url( 'edit.php' ) ) );
			}
		}

		/**
		 * Registers a post type.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function register_post_type() {
			$labels = array(
				'name'                => 'WPfomify',
				'singular_name'       => 'WPfomify',
				'add_new'             => esc_html__( 'Add New', 'ibx-wpfomo' ),
				'add_new_item'        => esc_html__( 'Add New', 'ibx-wpfomo' ),
				'edit_item'           => esc_html__( 'Edit', 'ibx-wpfomo' ),
				'new_item'            => esc_html__( 'New', 'ibx-wpfomo' ),
				'view_item'           => esc_html__( 'View', 'ibx-wpfomo' ),
				'search_items'        => esc_html__( 'Search', 'ibx-wpfomo' ),
				'not_found'           => esc_html__( 'No fomo found', 'ibx-wpfomo' ),
				'not_found_in_trash'  => esc_html__( 'No fomo found in Trash', 'ibx-wpfomo' ),
				'menu_name'           => 'WPfomify',
			);
			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => '',
				'taxonomies' 		  => array( '' ),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 80,
				'menu_icon'           => IBX_WPFOMO_URL . 'assets/img/wp-fomo-icon.png',
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => true,
				'can_export'          => true,
				'rewrite'             => '',
				'capability_type'     => 'post',
				'supports'            => array( 'title' ),
			);

			register_post_type( self::$type, $args );

			self::load_metabox();
		}

		/**
		 * Display appearance meta box
		 *
		 * @since 1.0.0
		 */
		static public function load_metabox() {
			require_once IBX_WPFOMO_DIR . 'includes/metabox.php';

			add_action( 'post_submitbox_misc_actions', __CLASS__ . '::add_active_check_field' );
		}

		/**
		 * Add tracking meta box.
		 *
		 * @since 1.0.0
		 */
		static public function add_tracking_metabox() {
			add_meta_box( 'ibx-wpfomo-tracking', __( 'Tracking', 'ibx-wpfomo' ), __CLASS__ . '::render_tracking_metabox', self::$type, 'side', 'default' );
		}

		/**
		 * Render active_check hidden field.
		 *
		 * @since 2.0.0
		 */
		static public function add_active_check_field( $post ) {
			if ( ! self::is_valid_screen() ) {
				return;
			}

			$status = get_post_meta( $post->ID, 'ibx_wpfomo_active_check', true );

			if ( false === $status || ! get_post_meta( $post->ID, 'ibx_wpfomo_type', true ) ) {
				$status = 1;
			}
			?>
			<input type="hidden" name="ibx_wpfomo_active_check" value="<?php echo $status; ?>" />
			<style>
				#misc-publishing-actions .misc-pub-visibility {
					display: none !important;
				}
			</style>
			<?php
		}

		/**
		 * Tracking fields.
		 */
		static public function get_tracking_fields() {
			$fields = array(
				'utm_source' => array(
					'type'          => 'text',
					'label'         => __( 'Campaign Source', 'ibx-wpfomo' ),
					'default'       => '',
					'placeholder'	=> 'utm_source',
					'help'   		=> __( 'Identify the advertiser, site, etc.', 'ibx-wpfomo' ),
					'priority'		=> 50,
				),
				'utm_medium'	=> array(
					'type'          => 'text',
					'label'         => __( 'Marketing Medium', 'ibx-wpfomo' ),
					'default'       => '',
					'placeholder'	=> 'utm_medium',
					'help'   		=> __( 'For example: banner, widget.', 'ibx-wpfomo' ),
					'priority'		=> 100,
				),
			);

			return $fields;
		}

		/**
		 * Render tracking metabox content.
		 *
		 * @param object WP_Post $post
		 */
		static public function render_tracking_metabox( $post ) {
			$fields = self::get_tracking_fields();
			?>
			<div class="mbt-metabox-tabs-wrapper">
				<table class="mbt-metabox-form-table">
				<?php foreach ( $fields as $name => $field ) { ?>
					<?php MetaBox_Tabs::render_metabox_field( $name, $field, get_post_meta( $post->ID, 'ibx_wpfomo_' . $name, true ) ); ?>
				<?php } ?>
				</table>
			</div>
			<?php
		}

		/**
		 * Set tracking fields to main metabox settings.
		 *
		 * @param array $fields
		 * @return array
		 */
		static public function set_tracking_fields( $fields ) {
			foreach ( self::get_tracking_fields() as $name => $field ) {
				$fields[ $name ] = $field;
			}

			return $fields;
		}

		/**
		 * Render admin post columns.
		 *
		 * @since 1.0.0
		 * @param array $columns
		 * @return array
		 */
		static public function add_post_custom_column( $columns ) {
			$title_column = $columns['title'];
			$date_column = $columns['date'];

			unset( $columns['title'] );
			unset( $columns['date'] );

			$columns['notification_status'] = '';
			$columns['title'] = $title_column;

			$columns['notification_type'] = __( 'Type', 'ibx-wpfomo' );

			$columns['date'] = $date_column;

			return apply_filters( 'ibx_wpfomo_post_columns', $columns );
		}

		/**
		 * Render content for admin post columns.
		 *
		 * @since 1.0.0
		 * @param string $column
		 * @param int $post_id
		 */
		static public function manage_post_custom_column( $column, $post_id ) {
			switch ( $column ) {
				case 'notification_type':
					$type = self::get_post_meta( $post_id, 'type' );
					if ( $type ) {
						echo IBX_WPFomo_Helper::get_notification_types( $type );
					}
					break;
				case 'notification_status':
					$status = self::get_post_meta( $post_id, 'active_check' );
					self::notification_toggle_control( $status, $post_id );
					break;
			}

			do_action( 'ibx_wpfomo_post_columns_content', $column, $post_id );
		}

		static public function notification_toggle_control( $status = '1', $post_id ) {
			$text           = __( 'Active', 'ibx-wpfomo' );
			$img_active     = IBX_WPFOMO_URL . 'assets/img/active1.png';
			$img_inactive   = IBX_WPFOMO_URL . 'assets/img/active0.png';
			$active         = 'true';
			$img            = $img_active;

			if ( ! $status ) {
				$text   = __( 'Inactive', 'ibx-wpfomo' );
				$img    = $img_inactive;
				$active = 'false';
			}
			?>
			<img src="<?php echo $img; ?>" style="cursor: pointer; height: 16px; vertical-align: middle;" alt="<?php echo $text; ?>" title="<?php echo $text; ?>" data-nonce="<?php echo wp_create_nonce( 'ibx_wpfomo_toggle_status' ); ?>" data-post="<?php echo $post_id; ?>" />
			<?php
		}

		/**
		 * Enqueue styles and scripts.
		 *
		 * @since 1.0.0
		 * @param string $hook
		 * @return void
		 */
		static public function load_scripts( $hook ) {
			global $post_type;

			wp_register_script( 'ibx-wpfomo-helper-script', IBX_WPFOMO_URL . 'assets/js/helper.js', array( 'jquery' ), IBX_WPFOMO_VER );
			wp_register_script( 'ibx-wpfomo-admin-settings-script', IBX_WPFOMO_URL . 'assets/js/admin-settings.js', array( 'jquery' ), IBX_WPFOMO_VER, true );

			wp_register_style( 'ibx-wpfomo-admin-style', IBX_WPFOMO_URL . 'assets/css/admin.css', array(), IBX_WPFOMO_VER );
			wp_register_script( 'ibx-wpfomo-admin-script', IBX_WPFOMO_URL . 'assets/js/admin.js', array( 'jquery' ), IBX_WPFOMO_VER, true );

			if ( 'post-new.php' == $hook || 'post.php' == $hook || 'edit.php' == $hook ) {

				if ( self::$type == $post_type ) {

					wp_enqueue_script( 'ibx-wpfomo-helper-script' );

					do_action( 'ibx_wpfomo_admin_meta_scripts' );

					wp_enqueue_style( 'ibx-wpfomo-admin-style' );
					wp_enqueue_script( 'ibx-wpfomo-admin-script' );
				}
			}

			if ( in_array( $hook, apply_filters( 'ibx_wpfomo_admin_pages_hook', array( 'ibx_wpfomo_page_wpfomo-settings' ) ) ) ) {

				wp_enqueue_style( 'ibx-wpfomo-admin-style' );

				wp_enqueue_script( 'ibx-wpfomo-helper-script' );
				wp_enqueue_script( 'ibx-wpfomo-admin-settings-script' );

				do_action( 'ibx_wpfomo_admin_settings_scripts' );
			}
		}

		/**
		 * Forces the WPFomo menu icon width/height for Retina devices.
		 *
		 * @since 1.0.0
		 */
		static public function admin_style() {
			?>
			<style type="text/css">#menu-posts-ibx_wpfomo .wp-menu-image img { width: 16px; height: 16px; }</style>
			<?php
		}

		/**
		 * Render settings menu in admin menu.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function admin_settings_menu() {
			if ( ! count( self::$settings ) ) {
				return;
			}

			$settings = apply_filters( 'ibx_wpfomo_admin_menu', array(
				'wpfomo-settings'   => array(
					'title'             => __( 'Settings', 'ibx-wpfomo' ),
					'capability'        => 'delete_users',
					'callback'          => __CLASS__ . '::render_settings_page',
				),
			) );

			foreach ( $settings as $slug => $setting ) {
				$cap = isset( $setting['capability'] ) ? $setting['capability'] : 'delete_users';
				if ( current_user_can( $cap ) ) {
					$hook = add_submenu_page( 'edit.php?post_type=ibx_wpfomo', $setting['title'], $setting['title'], $cap, $slug, $setting['callback'] );
					if ( isset( $setting['hook'] ) && is_callable( $setting['hook'] ) ) {
						add_action( 'load-' . $hook, $setting['hook'] );
					}
				}
			}
		}

		/**
		 * Render settings page.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function render_settings_page() {
			$current_tab = ( isset( $_GET['tab'] ) ) ? $_GET['tab'] : '';
			$tabs = self::$settings;

			if ( empty( $current_tab ) ) {

				if ( ! count( $tabs ) ) {
					return;
				}

				foreach ( $tabs as $tab => $data ) {
					if ( empty( $current_tab ) ) {
						$current_tab = $tab;
					} else {
						break;
					}
				}
			}

			$settings = self::get_settings();

			include IBX_WPFOMO_DIR . 'includes/admin-settings.php';
		}

		/**
		* Get saved settings.
		*
		* @since 1.0.0
		* @return array
		*/
		static public function get_settings( $key = '' ) {
			$fields         = self::get_settings_fields();
			$prefix         = 'ibx_wpfomo_';
			$settings       = get_option( $prefix . 'settings' );
			$new_settings   = array();

			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			if ( ! empty( $key ) ) {
				if ( isset( $settings[ $key ] ) ) {
					return $settings[ $key ];
				} else {
					return '';
				}
			}

			foreach ( $fields as $name => $field ) {

				$field_id   = $prefix . $name;
				$default    = isset( $field['default'] ) ? $field['default'] : '';

				if ( isset( $settings[ $name ] ) ) {
					$value  = $settings[ $name ];
				} else {
					$value  = $default;
				}

				$new_settings[ $name ] = $value;
			}

			return $new_settings;
		}

		/**
		* Get settings fields.
		*
		* @since 1.0.0
		* @return array
		*/
		static public function get_settings_fields() {
			$settings = self::$settings;
			$fields = array();

			foreach ( $settings as $tab ) {
				if ( isset( $tab['sections'] ) ) {
					foreach ( $tab['sections'] as $section ) {
						if ( isset( $section['fields'] ) ) {
							foreach ( $section['fields'] as $name => $field ) {
								$fields[ $name ] = $field;
							}
						}
					}
				}
			}

			return $fields;
		}

		/**
		* Register settings.
		*
		* @since 1.0.0
		* @param array $settings
		* @return void
		*/
		static public function register_settings( $settings = array() ) {
			if ( ! is_array( $settings ) || empty( $settings ) ) {
				return;
			}

			self::$settings = array_merge( self::$settings, $settings );

			if ( isset( $_REQUEST['page'] ) && 'wpfomo-settings' == sanitize_key( $_REQUEST['page'] ) ) {
				self::update_settings();
			}
		}

		/**
		* Register general settings.
		*
		* @since 1.0.0
		* @return void
		*/
		static public function register_settings_fields() {
			$settings = array(
				'general' => apply_filters( 'ibx_wpfomo_admin_general_settings', array(
					'title' => __( 'General', 'ibx-wpfomo' ),
					'show'  => IBX_WPFomo_Helper::is_pro_version(),
					'sections'  => array(
						'license'   => array(
							'title'     => __( 'General', 'ibx-wpfomo' ),
							'fields'    => array(),
						),
					),
				) ),
				'misc'	=> apply_filters( 'ibx_wpfomo_admin_misc_settings', array(
					'title'	=> __( 'Misc', 'ibx-wpfomo' ),
					'show'	=> true,
					'sections'	=> array(
						'notification'	=> array(
							'title'		=> __( 'Notification', 'ibx-wpfomo' ),
							'fields'	=> array(
								'translate_someone'	=> array(
									'type'				=> 'text',
									'label'				=> __( 'Translate "Someone"', 'ibx-wpfomo' ),
									'help'				=> __( 'If there is no person name in the notification, it will display this translated text instead of "Someone".', 'ibx-wpfomo' ),
								),
								'translate_ago'	=> array(
									'type'				=> 'text',
									'label'				=> __( 'Translate "ago"', 'ibx-wpfomo' ),
									'help'				=> __( 'It will be used for time text in notifications. If not required place "-" (hyphen).', 'ibx-wpfomo' ),
								),
							),
						),
						'exclude_users'	=> array(
							'title'			=> __( 'Exclude Users', 'ibx-wpfomo' ),
							'fields'		=> array(
								'exclude_emails'	=> array(
									'type'				=> 'textarea',
									'label'				=> __( 'Exclude these emails', 'ibx-wpfomo' ),
									'rows'				=> 8,
									'help'				=> __( 'Enter emails of the users whose identity need to be hidden. One email per line.', 'ibx-wpfomo' ),
								),
							),
						),
						'misc'	=> array(
							'title'		=> __( 'Miscellaneous', 'ibx-wpfomo' ),
							'fields'	=> array(
								'cache_duration'		=> array(
									'type'					=> 'text',
									'label'					=> __( 'Cache Duration', 'ibx-wpfomo' ),
									'default'				=> 45,
									'description'			=> __( 'minutes', 'ibx-wpfomo' ),
									'help'					=> __( 'If you are using add-ons, you can set the cache duration to automatically renew the data after given time. Minimum value should not be less than 5 minutes.', 'ibx-wpfomo' ),
									'class'					=> 'ibx-wpfomo-input-small',
								),
								'credit_link_disable'	=> array(
									'type'					=> 'checkbox',
									'label'					=> __( 'Disable Credit Link', 'ibx-wpfomo' ),
									'default'				=> 0,
									'help'					=> __( 'If checked, it will hide the "Powered by WPfomify" from the notification.', 'ibx-wpfomo' ),
									'render'				=> IBX_WPFomo_Helper::is_pro_version(),
								),
							),
						),
					),
				) ),
				'advanced' => apply_filters( 'ibx_wpfomo_admin_advanced_settings', array(
					'title'     => __( 'Advanced', 'ibx-wpfomo' ),
					'show'      => false,
					'sections'  => array(
						'api'       => array(
							'title'     => __( 'API', 'ibx-wpfomo' ),
							'fields'    => array(
								'api_key'   => array(
									'type'      => 'text',
									'label'     => __( 'Your API Key', 'ibx-wpfomo' ),
									'default'   => IBX_WPFomo_Helper::api_key(),
									'readonly'  => true,
									'disabled'	=> true,
									'clickselect'    => true,
								),
							),
						),
					),
				) ),
			);

			self::register_settings( $settings );
		}

		/**
		* Update settings.
		*
		* @since 1.0.0
		* @return void
		*/
		static private function update_settings() {
			if ( ! is_admin() ) {
				return;
			}

			// Only admins can save settings.
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			// Verify nonce.
			if ( ! isset( $_POST['ibx_wpfomo_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ibx_wpfomo_settings_nonce'] ), '_ibx_wpfomo_settings_nonce' ) ) {
				return;
			}

			// Save license.
			if ( isset( $_POST['ibx_wpfomo_license_key'] ) ) {
				$license_key = wpfomo_sanitize_license( $_POST['ibx_wpfomo_license_key'] );
				update_option( 'ibx_wpfomo_license_key', esc_attr( $license_key ) );
			}

			// Save credit link.
			if ( isset( $_POST['ibx_wpfomo_credit_link_disable'] ) ) {
				update_option( 'ibx_wpfomo_credit_link_disable', '1' );
			} else {
				delete_option( 'ibx_wpfomo_credit_link_disable' );
			}

			// Save cache duration.
			if ( isset( $_POST['ibx_wpfomo_cache_duration'] ) ) {
				update_option( 'ibx_wpfomo_cache_duration', sanitize_text_field( $_POST['ibx_wpfomo_cache_duration'] ) );
			}

			// Get settings fields.
			$fields = self::get_settings_fields();

			// Return if there are no fields.
			if ( empty( $fields ) ) {
				return;
			}

			// Get saved settings.
			$settings = self::get_settings();

			foreach ( $fields as $name => $field ) {

				$field_id   = 'ibx_wpfomo_' . $name;
				$value      = '';

				if ( isset( $_POST[ $field_id ] ) ) {
					if ( isset( $field['sanitize_custom'] ) && ! empty( $field['sanitize_custom'] ) ) {
						if ( is_callable( $field['sanitize_custom'] ) ) {
							$value = call_user_func( $field['sanitize_custom'], $_POST[ $field_id ] );
						}
					} else {
						$value = MetaBox_Tabs::sanitize_field( $field, $_POST[ $field_id ] );
					}
				} else {
					if ( 'checkbox' == $field['type'] ) {
						$value = '0';
					}
				}

				$settings[ $name ] = $value;
			}

			// Update option with settings data.
			update_option( 'ibx_wpfomo_settings', $settings );
		}

		/**
		 * Renders tabs for admin settings.
		 *
		 * @since 1.0.0
		 * @param string $current_tab
		 * @return void
		 */
		static public function render_settings_tabs( $current_tab = '' ) {
			if ( empty( self::$settings ) ) {
				return;
			}

			$tabs = self::$settings;

			foreach ( $tabs as $tab => $data ) {
				if ( ! $current_tab || empty( $current_tab ) ) {
					$current_tab = $tab;
				}
				if ( $data['show'] ) {
					?>
					<a href="<?php echo self::get_form_action( '&tab=' . $tab ); ?>" class="nav-tab<?php echo ( $current_tab == $tab ? ' nav-tab-active' : '' ); ?>"><?php echo $data['title']; ?></a>
					<?php
				}
			}
		}

		/**
		 * Render settings field.
		 *
		 * @since 1.0.0
		 * @param string $name
		 * @param array $field
		 * @param mixed $value
		 * @return void
		 */
		static public function render_settings_field( $name, $field, $value = '' ) {
			if ( ! $name || empty( $name ) ) {
				return;
			}
			if ( ! is_array( $field ) || empty( $field ) ) {
				return;
			}

			if ( 'license_key' == $name ) {
				return;
			}

			$default = isset( $field['default'] ) ? $field['default'] : '';

			if ( empty( $value ) ) {
				$settings = self::get_settings();
				if ( isset( $settings[ $name ] ) ) {
					$value = $settings[ $name ];
				} else {
					$value = $default;
				}
			}

			MetaBox_Tabs::render_metabox_field( $name, $field, $value );
		}

		/**
		 * Renders the update message.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function render_update_message() {
			if ( ! empty( $_POST ) && ! isset( $_POST['email'] ) ) {
				echo '<div class="updated"><p>' . esc_html__( 'Settings updated!', 'ibx-wpfomo' ) . '</p></div>';
			}
		}

		/**
		 * Renders the action for a form.
		 *
		 * @since 1.0.0
		 * @param string $query_var
		 * @return void
		 */
		static public function get_form_action( $query_var = '' ) {
			$page = '/edit.php?post_type=ibx_wpfomo&page=wpfomo-settings';

			if ( is_network_admin() ) {
				return network_admin_url( $page . $query_var );
			} else {
				return admin_url( $page . $query_var );
			}
		}

		/**
		 * Notification Preview.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function preview() {
			if ( self::is_valid_screen() ) {

				$post_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';

				if ( empty( $post_id ) ) {
					global $post;
					$post_id = $post->ID;
				}

				$settings = MetaBox_Tabs::get_metabox_settings( $post_id );

				include IBX_WPFOMO_DIR . 'includes/admin-preview.php';

				do_action( 'ibx_wpfomo_admin_preview', $settings );
			}
		}

		/**
		 * Render settings JS.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static public function render_settings_js() {
			if ( self::is_valid_screen() ) {

				$post_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : '';

				if ( empty( $post_id ) ) {
					global $post;
					$post_id = $post->ID;
				}

				$settings = MetaBox_Tabs::get_metabox_settings( $post_id );

				echo '<script type="text/javascript">';
				echo 'var ibx_wpfomo_settings = ' . json_encode( $settings );
				echo '</script>';
			}
		}

		/**
		 * Admin notices.
		 *
		 * @since 1.1.1
		 * @return void
		 */
		static public function admin_notices() {
			if ( ! is_admin() ) {
				return;
			} elseif ( ! is_user_logged_in() ) {
				return;
			} elseif ( ! current_user_can( 'update_core' ) ) {
				return;
			}

			if ( count( self::$errors ) ) {
				foreach ( self::$errors as $key => $msg ) {
					?>
					<div class="notice notice-error">
						<p><?php echo $msg; ?></p>
					</div>
					<?php
				}
			}
		}

		/**
		 * Custom functionality on post save.
		 *
		 * @since 2.0
		 * @param int $post_id
		 * @param string $prefix
		 */
		static public function save_post( $post_id, $prefix, $fields, $settings ) {
			$toggle_sections = get_post_meta( $post_id, 'mbt_section_toggle', true );

			foreach ( $fields as $key => $field ) {
				if ( isset( $field['_meta'] ) ) {
					$section = $field['_meta']['section']['name'];
					$collapsable = $field['_meta']['section']['collapsable'];
					if ( ! isset( $toggle_sections[ $section ] ) && $collapsable ) {

						if ( isset( $field['default'] ) ) {
							$field_default = $field['default'];
						} else {
							if ( 'checkbox' === $field['type'] ) {
								$field_default = '0';
							} else {
								$field_default = '';
							}
						}
						$conversion_source = IBX_WPFomo_Admin::get_post_meta( $post_id, 'conversions_source' );
						update_post_meta( $post_id, $prefix . $key, $field_default );
					}
				}
			}

			if ( isset( $_POST['ibx_wpfomo_active_check'] ) ) {
				update_post_meta( $post_id, 'ibx_wpfomo_active_check', 1 );
			}

			do_action( 'ibx_wpfomo_save_post', $post_id, $prefix, $settings );
		}

		static public function render_metabox_footer( $post_id, $args ) {
			?>
			<div class="mbt-metabox-tabs-footer">
				<div class="ibx-wpfomo-tab-navigation">
					<a href="javascript:void(0)" class="ibx-wpfomo-next-tab"><?php _e( 'Next', 'ibx-wpfomo' ); ?><span class="dashicons dashicons-arrow-right-alt"></span></a>
					<a href="javascript:void(0)" class="ibx-wpfomo-save-config" data-saving="<?php _e( 'Saving...', 'ibx-wpfomo' ); ?>"><?php _e( 'Save', 'ibx-wpfomo' ); ?></a>
				</div>
			</div>
			<?php
		}

		/**
		* Add custom inverval for cron.
		*
		* @since 1.1.3
		* @param array $schedules
		*/
		static public function custom_cache_duration( $schedules ) {
			$custom_duration = get_option( 'ibx_wpfomo_cache_duration' );

			if ( ! $custom_duration || empty( $custom_duration ) ) {
				$custom_duration = 45;
			}

			if ( $custom_duration < 5 ) {
				$custom_duration = 5;
			}

			$schedules['wpfomify_cache_interval'] = array(
				'interval'	=> $custom_duration * 60,
				// translators: %s for custom duration.
				'display'	=> sprintf( __( 'Every %s minutes', 'ibx-wpfomo' ), $custom_duration ),
			);

			return $schedules;
		}

		/**
		* Get value from post meta.
		*
		* @since 1.0.0
		* @param int $post_id
		* @param string $key
		* @param bool $single
		* @return mixed
		*/
		static public function get_post_meta( $post_id, $key, $single = true ) {
			return get_post_meta( $post_id, 'ibx_wpfomo_' . $key, $single );
		}

		/**
		* Get value from post meta.
		*
		* @since 1.0.0
		* @param int $post_id
		* @param string $key
		* @param mixed $value
		* @return void
		*/
		static public function update_post_meta( $post_id, $key, $value ) {
			update_post_meta( $post_id, 'ibx_wpfomo_' . $key, $value );
		}

		/**
		 * Check if is valid screen.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		static private function is_valid_screen() {
			global $pagenow;

			if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
				return false;
			}

			global $post_type;

			if ( self::$type != $post_type ) {
				return false;
			}

			return true;
		}
	}

	// Initialize the class.
	IBX_WPFomo_Admin::init();
} // End if().
