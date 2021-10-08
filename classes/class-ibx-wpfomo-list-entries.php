<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles logic for list entries.
 *
 * @since 1.0.0
 */
class IBX_WPFomo_List_Entries {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Holds entries WP_List_Table object.
	 *
	 * @since 1.0.0
	 * @var array $entries_list
	 */
	public $entries_list;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		}
		add_action( 'plugins_loaded', array( $this, 'init_hooks' ) );
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 */
	public function init_hooks() {
		require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-list-helper.php';
		require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-list.php';

		add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );
		add_action( 'ibx_wpfomo_admin_menu', array( $this, 'menu' ), 10, 1 );
		add_action( 'admin_menu', array( $this, 'remove_menu' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		//add_filter( 'ibx_wpfomo_admin_advanced_settings', array( $this, 'enable_advanced_settings' ), 10, 1 );
		add_action( 'wp_ajax_ibx_wpfomo_custom_form_data_edit', array( $this, 'save_ajax_data' ) );
	}

	/**
	 * Screen options for entries.
	 *
	 * @since 1.0.0
	 */
	public function set_screen( $status, $option, $value ) {
		return $value;
	}

	/**
	 * Add admin page for the entries view.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function menu( $args ) {
		$args['wpfomo-custom-form-data'] = array(
			'title'      => __( 'Entries', 'ibx-wpfomo' ),
			'capability' => 'manage_options',
			'callback'   => array( $this, 'render_entries_page' ),
			'hook'       => array( $this, 'screen_option' ),
		);

		return $args;
	}

	/**
	 * Remove entries page menu from admin menu.
	 *
	 * @since 1.0.0
	 */
	public function remove_menu() {
		$post_type = IBX_WPFomo_Admin::$type;
		remove_submenu_page( 'edit.php?post_type=' . $post_type, 'wpfomo-custom-form-data' );
	}

	/**
	 * Set screen option for Entries Per Page.
	 *
	 * @since 1.0.0
	 */
	public function screen_option() {
		$option = 'per_page';
		$args   = array(
			'label'   => __( 'Entries Per Page', 'ibx-wpfomo' ),
			'default' => 10,
			'option'  => 'ibx_wpfomo_custom_form_entries_per_page',
		);

		add_screen_option( $option, $args );

		$this->entries_list = new IBX_WPFomo_List();
	}

	public function admin_scripts( $hook ) {
		if ( 'ibx_wpfomo_page_wpfomo-custom-form-data' == $hook ) {
			wp_enqueue_script( 'ibx-wpfomo-admin-list-table', IBX_WPFOMO_URL . 'assets/js/admin-list-table.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
		}
	}

	/**
	 * Render entries page.
	 *
	 * @since 1.0.0
	 */
	public function render_entries_page() {
		?>
		<div class="wrap">

			<h2><?php _e( 'Custom Entries', 'ibx-wpfomo' ); ?></h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->entries_list->prepare_items();
								$this->entries_list->display();
								?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>

		</div>
		<?php
	}

	/**
	 * Show advanced settings tab.
	 *
	 * @since 1.0.0
	 * @param array $args
	 * @return array
	 */
	public function enable_advanced_settings( $args ) {
		$args['show'] = true;

		return $args;
	}

	/**
	 * Save AJAX response.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function save_ajax_data() {
		if ( ! isset( $_POST['entry_id'] ) || empty( $_POST['entry_id'] ) ) {
			wp_send_json_error( __( 'Entry ID is missing.', 'ibx-wpfomo' ) );
		}
		if ( ! isset( $_POST['post_id'] ) || empty( $_POST['post_id'] ) ) {
			wp_send_json_error( __( 'Post ID is missing.', 'ibx-wpfomo' ) );
		}

		$entry_id = sanitize_key( wp_unslash( $_POST['entry_id'] ) );
		$post_id  = absint( wp_unslash( $_POST['post_id'] ) );

		$updated = 0;

		if ( isset( $_POST['entry_id'] ) && isset( $_POST['post_id'] ) ) {
			$new_data = array(
				'ip_address' => ! empty( $_POST['ip_address'] ) ? sanitize_text_field( wp_unslash( $_POST['ip_address'] ) ) : '',
				'id'         => $entry_id,
				'post_id'    => $post_id,
			);

			if ( isset( $_POST['time'] ) && ! empty( $_POST['time'] ) ) {
				$new_data['time'] = $_POST['time'];
			}

			if ( ! empty( $_POST['name'] ) ) {
				$new_data['name'] = sanitize_text_field( wp_unslash( $_POST['name'] ) );
			}
			if ( ! empty( $_POST['email'] ) ) {
				$new_data['email'] = sanitize_email( wp_unslash( $_POST['email'] ) );
			}
			if ( ! empty( $_POST['title'] ) ) {
				$new_data['title'] = sanitize_text_field( wp_unslash( $_POST['title'] ) );
			}

			if ( ! empty( $_POST['city'] ) ) {
				$new_data['city'] = sanitize_text_field( wp_unslash( $_POST['city'] ) );
			}
			if ( ! empty( $_POST['state'] ) ) {
				$new_data['state'] = sanitize_text_field( wp_unslash( $_POST['state'] ) );
			}
			if ( ! empty( $_POST['country'] ) ) {
				$new_data['country'] = sanitize_text_field( wp_unslash( $_POST['country'] ) );
			}

			if ( IBX_WPFomo_Conversion::update_conversion_data( $entry_id, $new_data ) ) {
				$updated = 1;
			}
		}

		if ( $updated ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( __( 'Update failed!', 'ibx-wpfomo' ) );
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_List_Entries object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_List_Entries ) ) {
			self::$instance = new IBX_WPFomo_List_Entries();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_list_entries = IBX_WPFomo_List_Entries::get_instance();
