<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles logic for addons page and installation.
 *
 * @since 1.1.0
 */
final class IBX_WPFomo_Addons {

	static public $addons = array();

	/**
	 * Initialize class.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	static public function init() {
		add_action( 'plugins_loaded', __CLASS__ . '::init_hooks' );
	}

	/**
	 * Initialize hooks and filters.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	static public function init_hooks() {
		add_filter( 'ibx_wpfomo_admin_menu',                __CLASS__ . '::admin_menu', 10, 1 );
		add_filter( 'ibx_wpfomo_admin_pages_hook',          __CLASS__ . '::addons_page_hook', 10, 1 );
		add_action( 'wp_ajax_ibx_wpfomo_install_addon',     __CLASS__ . '::install_addon' );
		add_action( 'wp_ajax_ibx_wpfomo_activate_addon',    __CLASS__ . '::activate_addon' );
		add_action( 'wp_ajax_ibx_wpfomo_deactivate_addon',  __CLASS__ . '::deactivate_addon' );
	}

	/**
	 * Added addons page to admin menu.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	static public function admin_menu( $args ) {
		$args['wpfomo-addons'] = array(
			'title'             => __( 'Addons', 'ibx-wpfomo' ),
			'capability'        => 'delete_users',
			'callback'          => __CLASS__ . '::render_addons_page',
			'hook'              => __CLASS__ . '::init_addons_page',
		);

		return $args;
	}

	/**
	 * Addons admin page hook.
	 *
	 * @since 1.1.0
	 * @param array $hooks
	 * @return string
	 */
	static public function addons_page_hook( $hooks ) {
		$hooks[] = 'ibx_wpfomo_page_wpfomo-addons';

		return $hooks;
	}

	/**
	 * Initialize the addons page.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	static public function init_addons_page() {
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_scripts' );
		self::get_addons_data();
	}

	/**
	 * Render addon page.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	static public function render_addons_page() {
		// Get addons data.
		$addons = self::get_addons();
		// Get installed plugins.
		$installed_plugins = get_plugins();
		// Load template file.
		include IBX_WPFOMO_DIR . 'includes/admin-addons.php';
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	static public function enqueue_scripts() {
		wp_enqueue_style( 'ibx-wpfomo-addons', IBX_WPFOMO_URL . 'assets/css/admin-addons.css', array(), IBX_WPFOMO_VER );
		wp_enqueue_script( 'ibx-wpfomo-addons', IBX_WPFOMO_URL . 'assets/js/admin-addons.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
		wp_localize_script( 'ibx-wpfomo-addons', 'ibx_wpfomo_addons', array(
			'install_nonce'     => wp_create_nonce( 'ibx-wpfomo-install-addon' ),
			'activate_nonce'    => wp_create_nonce( 'ibx-wpfomo-activate-addon' ),
			'deactivate_nonce'  => wp_create_nonce( 'ibx-wpfomo-deactivate-addon' ),
			'install_text'      => esc_html__( 'Install', 'ibx-wpfomo' ),
			'installing_text'   => esc_html__( 'Installing...', 'ibx-wpfomo' ),
			'activate_text'     => esc_html__( 'Activate', 'ibx-wpfomo' ),
			'activating_text'   => esc_html__( 'Activating...', 'ibx-wpfomo' ),
			'deactivate_text'   => esc_html__( 'Deactivate', 'ibx-wpfomo' ),
			'deactivating_text' => esc_html__( 'Deactivating...', 'ibx-wpfomo' ),
			'proceed_text'      => esc_html__( 'Proceed', 'ibx-wpfomo' ),
			'connect_error'     => esc_html__( 'Unable to connect to the filesystem. Please confirm your credentials.', 'ibx-wpfomo' ),
		) );
	}

	/**
	 * Retrieve the plugin basename from the plugin slug.
	 *
	 * @since 1.1.0
	 *
	 * @param string $slug The plugin slug.
	 * @return string $slug	The plugin basename if found, else the plugin slug.
	 */
	static public function get_plugin_basename_from_slug( $slug ) {
		$keys = array_keys( get_plugins() );

		foreach ( $keys as $key ) {
			if ( preg_match( '|^' . $slug . '|', $key ) ) {
				return $key;
			}
		}

		return $slug;
	}

	/**
	 * Fetch addons data from remote server and store in transient.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	static public function get_addons_data() {
		$url = 'https://wpfomify.com/wp-json/wpfomify/v2/addons/';
		$license = trim( get_option( 'ibx_wpfomo_license_key' ) );
		$addons = array();

		// Get template data response.
		$response = wp_remote_post( $url, array(
			'timeout'       => 30,
			'sslverify'     => false,
			'httpversion'   => '1.1',
			'body'          => array(
				'license'       => $license,
				'url'           => home_url(),
				'beta'			=> true,
				'exclude'		=> array(
					'wpfomify-woocommerce',
					'wpfomify-edd',
					'wpfomify-gravityforms',
					'wpfomify-give',
					'wpfomify-learndash',
					'wpfomify-lifterlms',
				),
			),
		) );

		if ( is_wp_error( $response ) ) {
			IBX_WPFomo_Admin::$errors[] = $response->get_error_message();
			set_transient( '_ibx_wpfomo_addons', $addons, DAY_IN_SECONDS );
			return $addons;
		}

		// JSON decode data.
		$data = json_decode( wp_remote_retrieve_body( $response ), 1 );

		// Return if no valid data.
		if ( array_key_exists( 'code', $data ) ||
				array_key_exists( 'message', $data ) ||
				array_key_exists( 'data', $data ) ||
				! count( $data )
		) {
			IBX_WPFomo_Admin::$errors[] = esc_html__( 'Addons are not available at the moment.', 'ibx-wpfomo' );
			set_transient( '_ibx_wpfomo_addons', $addons, DAY_IN_SECONDS );
			return $addons;
		}

		// Otherwise, our request worked. Save the data and return it.
		set_transient( '_ibx_wpfomo_addons', $data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Get addons from transient data.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	static public function get_addons() {
		if ( isset( $_GET['reload'] ) ) {
			return self::get_addons_data();
		}

		// Get addons from our transient data.
		$addons = get_transient( '_ibx_wpfomo_addons' );

		if ( false === $addons || ! is_array( $addons ) || empty( $addons ) ) {
			// It wasn't there, so regenerate the data and save the transient.
			$addons = self::get_addons_data();
		}

		/* Exclude addons which have been added to the core plugin */
		$filtered_addons = array();
		$exclude_addons = array( 'wpfomify-woocommerce', 'wpfomify-edd', 'wpfomify-gravityforms', 'wpfomify-give', 'wpfomify-learndash', 'wpfomify-lifterlms' );

		foreach ( $addons as $addon ) {
			$addon['slug'] = str_replace( '-new', '', $addon['slug'] );

			if ( in_array( $addon['slug'], $exclude_addons ) ) {
				continue;
			}

			$filtered_addons[] = $addon;
		}

		return $filtered_addons;
	}

	/**
	 * Install addon via AJAX.
	 *
	 * @since 1.1.0
	 */
	static public function install_addon() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ibx-wpfomo-install-addon' ) ) {
			echo json_encode( array(
				'error' => esc_html__( 'Invalid request.', 'ibx-wpfomo' ),
			) );
			die;
		}

		// install the addon.
		if ( isset( $_POST['addon_url'] ) ) {
			$addon_url = esc_url( $_POST['addon_url'] );
			global $hook_suffix;

			// Set the current screen to avoid undefined notices.
			set_current_screen();

			// Prepare variables.
			$method = '';
			$url    = add_query_arg(
				array(
					'page' => 'wpfomo-addons',
				),
				admin_url( 'admin.php' )
			);
			$url = esc_url( $url );

			// Start output bufferring to catch the filesystem form if credentials are needed.
			ob_start();
			$creds = request_filesystem_credentials( $url, $method, false, false, null );

			if ( false === $creds ) {
				$form = ob_get_clean();
				echo json_encode( array(
					'form' => $form,
				) );
				die;
			}

			// If we are not authenticated, make it happen now.
			if ( ! WP_Filesystem( $creds ) ) {
				ob_start();
				request_filesystem_credentials( $url, $method, true, false, null );
				$form = ob_get_clean();
				echo json_encode( array(
					'form' => $form,
				) );
				die;
			}

			// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-skin.php';

			// Create the plugin upgrader with our custom skin.
			$installer = new Plugin_Upgrader( new IBX_WPFomo_Skin() );
			$installer->install( $addon_url );

			// Flush the cache and return the newly installed plugin basename.
			wp_cache_flush();
			if ( $installer->plugin_info() ) {
				$plugin_basename = $installer->plugin_info();
				echo json_encode( array(
					'plugin' => $plugin_basename,
					'success' => true,
				) );
				die;
			}
		} // End if().

		// Send back a response.
		wp_send_json_success();
	}

	/**
	 * Activate addon via AJAX.
	 *
	 * @since 1.1.0
	 */
	static public function activate_addon() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ibx-wpfomo-activate-addon' ) ) {
			echo json_encode( array(
				'error' => esc_html__( 'Invalid request.', 'ibx-wpfomo' ),
			) );
			die;
		}

		// Activate the addon.
		if ( isset( $_POST['plugin'] ) ) {
			$activate = activate_plugin( $_POST['plugin'] );

			if ( is_wp_error( $activate ) ) {
				echo json_encode( array(
					'error' => $activate->get_error_message(),
				) );
				die;
			}
		}

		wp_send_json_success();
	}

	/**
	 * Deactivate addon via AJAX.
	 *
	 * @since 1.1.0
	 */
	static public function deactivate_addon() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'ibx-wpfomo-deactivate-addon' ) ) {
			echo json_encode( array(
				'error' => esc_html__( 'Invalid request.', 'ibx-wpfomo' ),
			) );
			die;
		}

		// Deactivate the addon.
		if ( isset( $_POST['plugin'] ) ) {
			$deactivate = deactivate_plugins( $_POST['plugin'] );

			if ( is_wp_error( $deactivate ) ) {
				echo json_encode( array(
					'error' => $deactivate->get_error_message(),
				) );
				die;
			}
		}

		wp_send_json_success();
	}
}

// Initialize the class.
IBX_WPFomo_Addons::init();
