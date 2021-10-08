<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'IBX_WPFomo_Custom_Data_Migration' ) ) {
	/**
	 * Custom data migration.
	 *
	 * @since 2.0
	 */
	class IBX_WPFomo_Custom_Data_Migration {
		/**
		 * Holds the new db version.
		 *
		 * @since 2.0
		 * @var string $db_version
		 */
		public static $db_version = 1.1;

		/**
		 * Holds current db version.
		 *
		 * @since 2.0
		 * @var string $current_db_version
		 */
		public static $current_db_version;

		/**
		 * Holds boolean value migrated or not.
		 *
		 * @since 2.0
		 * @var boolean $is_migrated
		 */
		public static $is_migrated = false;

		/**
		 * Holds the error message.
		 *
		 * @since 2.0
		 * @var string $error
		 */
		public static $error = '';

		/**
		 * Holds post id of custom converstion source to migrate.
		 *
		 * @since 2.0
		 * @var array $custom_post
		 */
		public static $custom_post = array();

		/**
		 * Holds table name for converstion to migrate.
		 *
		 * @since 2.0
		 * @var string $table_conversion_data
		 */
		public static $table_conversion_data = '';

		/**
		 * Holds table name for converstion meta.
		 *
		 * @since 2.0
		 * @var string $table_conversion_meta
		 */
		public static $table_conversion_meta = '';

		/**
		 * Holds $wpdb.
		 *
		 * @since 2.0
		 * @var object $global_wpdb
		 */
		public static $global_wpdb;

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init() {
			global $wpdb;
			self::$global_wpdb = $wpdb;

			self::$table_conversion_data = $wpdb->prefix . 'ibx_wpfomo_conversion_data';
			self::$table_conversion_meta = $wpdb->prefix . 'ibx_wpfomo_conversion_meta';

			add_action( 'plugins_loaded', __CLASS__ . '::check_db_version' );
			add_action( 'admin_notices', __CLASS__ . '::render_error_notice' );
		}

		/**
		 * Get db version.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function get_db_version() {
			self::$current_db_version = get_site_option( 'ibx_wpfomo_db_version' );
		}

		/**
		 * Check db version.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function check_db_version() {
			self::get_db_version();

			if ( empty( self::$current_db_version ) ) {
				try {
					self::init_migration();
					self::$is_migrated = true;
				} catch ( Exception $e ) {
					// translators: %s is for error message.
					self::$error = sprintf( __( 'Migration failed: %s', 'ibx-wpfomo' ), $e->getMessage() );
				}
			}
		}

		/**
		 * Install db migration.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init_migration() {
			$tmp_sql     = self::$global_wpdb->prepare( "SHOW TABLES LIKE '%s'", self::$table_conversion_data );
			$tmp_results = self::$global_wpdb->get_results( $tmp_sql, ARRAY_A );
			if ( empty( $tmp_results ) ) {
				// Create custom table in DB if doesn't exist.
				$charset_collate = self::$global_wpdb->get_charset_collate();

				$sql = 'CREATE TABLE ' . self::$table_conversion_data . " (
                            id bigint(20) NOT NULL AUTO_INCREMENT,
                            post_id bigint(20) NULL DEFAULT NULL,
                            title varchar(100) NULL DEFAULT NULL,
                            name varchar(100) NULL DEFAULT NULL,
                            email varchar(100) NULL DEFAULT NULL,
                            time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                            city varchar(100) NULL DEFAULT NULL,
                            state varchar(100) NULL DEFAULT NULL,
                            country varchar(100) NULL DEFAULT NULL,
                            image_url tinytext NULL DEFAULT '',
                            url tinytext NULL DEFAULT '',
                            ip_address varchar(24) NULL DEFAULT NULL,
                            src text NULL DEFAULT NULL,                
                            PRIMARY KEY  (id),
							KEY idx_conv_data_email (email),
							KEY idx_conv_data_post_id (post_id)
                        ) $charset_collate;";

				$sql .= 'CREATE TABLE ' . self::$table_conversion_meta . " (
							id bigint(20) NOT NULL AUTO_INCREMENT,
                            email varchar(100) NOT NULL,                            
                            meta_key varchar(255) NULL DEFAULT NULL,
                            meta_value longtext NULL DEFAULT NULL,							                                           
                            PRIMARY KEY  (id),
							KEY idx_conv_meta_email (email)
						) $charset_collate;";
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';

				$res = dbDelta( $sql );

				if ( empty( $res ) ) {
					self::$error = esc_html_e( "Error creating database table.\n", 'ibx-wpfomo' );
				} else {
					add_option( 'ibx_wpfomo_db_version', self::$db_version );
				}
			} else {

			} // End if().
		}

		/**
		 * Error notice.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function render_error_notice() {
			if ( ! empty( self::$error ) ) {
				?>
				<div class="error notice">
					<p><?php echo self::$error; ?></p>
				</div>
				<?php
			}
		}

	}

	IBX_WPFomo_Custom_Data_Migration::init();
} // End if().
