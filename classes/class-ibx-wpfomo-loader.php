<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'IBX_WPFomo_Loader' ) ) {
	/**
	 * Responsible for setting up classes and includes.
	 *
	 * @since 2.0
	 */
	final class IBX_WPFomo_Loader {
		/**
		 * Holds the message.
		 *
		 * @since 2.0
		 * @var string $notice_msg
		 */
		public static $notice_msg = '';

		/**
		 * Holds the message type.
		 *
		 * @since 2.0
		 * @var string $notice_type
		 */
		public static $notice_type = 'info';

		/**
		 * Load the plugin.
		 *
		 * @since 2.0
		 * @return void
		 */
		public static function init() {
			add_action( 'admin_init', __CLASS__ . '::deactivate_addons' );

			self::load_files();
			self::load_extensions();
			self::update_meta();
		}

		/**
		 * Deactivate old add-ons which are merged in core.
		 *
		 * @since 2.0
		 * @return void
		 */
		public static function deactivate_addons() {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			// WooCommerce add-on.
			if ( defined( 'IBX_WPFOMO_WOOCOMMERCE_PATH' ) && is_plugin_active( IBX_WPFOMO_WOOCOMMERCE_PATH ) ) {
				deactivate_plugins( IBX_WPFOMO_WOOCOMMERCE_PATH );
			}

			// EDD add-on.
			if ( defined( 'IBX_WPFOMO_EDD_PATH' ) && is_plugin_active( IBX_WPFOMO_EDD_PATH ) ) {
				deactivate_plugins( IBX_WPFOMO_EDD_PATH );
			}

			// Give add-on.
			if ( defined( 'IBX_WPFOMO_GIVE_PATH' ) && is_plugin_active( IBX_WPFOMO_GIVE_PATH ) ) {
				deactivate_plugins( IBX_WPFOMO_GIVE_PATH );
			}

			// GravityForms add-on.
			if ( defined( 'IBX_WPFOMO_GFORMS_PATH' ) && is_plugin_active( IBX_WPFOMO_GFORMS_PATH ) ) {
				deactivate_plugins( IBX_WPFOMO_GFORMS_PATH );
			}

			// LearnDash add-on.
			if ( defined( 'IBX_WPFOMO_LEARNDASH_PATH' ) && is_plugin_active( IBX_WPFOMO_LEARNDASH_PATH ) ) {
				deactivate_plugins( IBX_WPFOMO_LEARNDASH_PATH );
			}

			// LifterLMS add-on.
			if ( defined( 'IBX_WPFOMO_LLMS_PATH' ) && is_plugin_active( IBX_WPFOMO_LLMS_PATH ) ) {
				deactivate_plugins( IBX_WPFOMO_LLMS_PATH );
			}
		}

		/**
		 * Loads classes and includes.
		 *
		 * @since 2.0
		 * @return void
		 */
		private static function load_files() {
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-helper.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-cron.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-admin.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-fields.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-conversion.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-ajax.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-frontend.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-floatingbutton.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-api.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-addons.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-addon.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-form-parser.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-custom-data-migration.php';
			require_once IBX_WPFOMO_DIR . 'classes/class-ibx-wpfomo-list-entries.php';
			require_once IBX_WPFOMO_DIR . 'includes/updater/updater-config.php';
		}

		/**
		 * Loads extensions.
		 *
		 * @since 2.0
		 * @return void
		 */
		public static function load_extensions( $path = null ) {
			$path       = $path ? trailingslashit( $path ) : IBX_WPFOMO_DIR . 'extensions/';
			$extensions = glob( $path . '*' );

			if ( ! is_array( $extensions ) ) {
				return;
			}

			foreach ( $extensions as $extension ) {

				if ( ! is_dir( $extension ) ) {
					continue;
				}

				$path = trailingslashit( $extension ) . basename( $extension ) . '.php';

				if ( file_exists( $path ) ) {
					require_once $path;
				}
			}
		}

		/**
		 * Update the value of new meta keys using the old keys value.
		 *
		 * @since 2.0
		 * @return void
		 */
		public static function update_meta() {
			if ( ! is_admin() ) {
				return;
			}

			// array of the data to be stored with new key.
			// array( 'option_key_suffix' => array( 'old_key' => array( 'new_key', 'fallback_value' ) ) )
			$raw_data = apply_filters( 'ibx_wpfomo_update_meta_once', array() );

			if ( ! is_array( $raw_data ) || empty( $raw_data ) ) {
				return;
			}

			$updated_meta = get_site_option( 'ibx_wpfomo_meta_updated' );

			if ( ! is_array( $updated_meta ) ) {
				$updated_meta = array();
			}

			$data_updated = false;

			global $wpdb;

			foreach ( $raw_data as $option_key => $conditional_data ) {

				// lets not proceed further if the data has already been updated.
				if ( in_array( $option_key, $updated_meta ) ) {
					continue;
				}

				// if $conditional_data is empty then return.
				if ( ! is_array( $conditional_data ) || empty( $conditional_data ) ) {
					continue;
				}

				if ( ! isset( $conditional_data['condition'] ) || ! isset( $conditional_data['fields'] ) ) {
					continue;
				}

				$data       = is_array( $conditional_data['fields'] ) && ! empty( $conditional_data['fields'] ) ? $conditional_data['fields'] : array();
				$conditions = is_array( $conditional_data['condition'] ) && ! empty( $conditional_data['condition'] ) ? $conditional_data['condition'] : array();

				if ( empty( $data ) || empty( $conditions ) ) {
					continue;
				}

				$count = 1;

				// Build the query to fetch the post IDs which match the conditions.
				$conditional_sql = "SELECT {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts";

				for ( $i = 1; $i <= count( $conditions ); $i++ ) {
					$conditional_sql .= " INNER JOIN {$wpdb->prefix}postmeta AS mt{$i} ON ( {$wpdb->prefix}posts.ID = mt{$i}.post_id ) ";
				}

				$conditional_sql .= 'WHERE 1=1 AND (';

				// Loop through the conditional meta keys and join the query.
				foreach ( $conditions as $cond_key => $cond_value ) {
					$conditional_sql .= " ( mt{$count}.meta_key = '{$cond_key}' AND mt{$count}.meta_value = '{$cond_value}' ) ";
					if ( count( $conditions ) !== $count ) {
						$conditional_sql .= ' AND';
					}
					$count++;
				}

				$conditional_sql .= ") AND {$wpdb->prefix}posts.post_type = 'ibx_wpfomo' GROUP BY {$wpdb->prefix}posts.ID ORDER BY {$wpdb->prefix}posts.post_date";

				// Fetch the results.
				$post_ids_db = $wpdb->get_results( $conditional_sql, ARRAY_A );

				if ( ! is_array( $post_ids_db ) || empty( $post_ids_db ) ) {
					continue;
				}

				$post_ids = array();

				foreach ( $post_ids_db as $post_id_array ) {
					if ( isset( $post_id_array['ID'] ) ) {
						$post_ids[] = $post_id_array['ID'];
					}
				}

				if ( empty( $post_ids ) ) {
					continue;
				}

				// Convert post IDs array into comma separated string.
				$post_ids_string = "'" . implode( "', '", $post_ids ) . "'";
				$metakeys        = "'" . implode( "', '", array_keys( $data ) ) . "'";

				$query = "SELECT * FROM $wpdb->postmeta WHERE `post_id` IN ({$post_ids_string}) AND `meta_key` IN ({$metakeys})";

				$metadata = $wpdb->get_results( $query, ARRAY_A );

				// lets update the new metakey with the value.
				if ( is_array( $metadata ) && count( $metadata ) ) {
					foreach ( $metadata as $meta ) {

						$post_id    = $meta['post_id'];
						$meta_key   = $meta['meta_key'];
						$meta_value = $meta['meta_value'];

						// validate the data.
						if ( ! is_array( $data[ $meta_key ] ) || empty( $data[ $meta_key ] ) ) {
							continue;
						}
						if ( empty( $meta_value ) && isset( $data[ $meta_key ][1] ) ) {
							$meta_value = $data[ $meta_key ][1];
						}

						update_post_meta( $post_id, $data[ $meta_key ][0], $meta_value );

						// update the section toggle meta in this case.
						update_post_meta(
							$post_id,
							'mbt_section_toggle',
							array(
								'timing'    => 1,
								'behaviour' => 1,
								'design'    => 1,
							)
						);
					}

					$updated_meta[] = $option_key;

					update_site_option( 'ibx_wpfomo_meta_updated', $updated_meta );

					$data_updated = true;
				}
			} // End foreach().

			if ( $data_updated ) {
				self::render_notice( __( 'WPfomify data updated successfully!', 'ibx-wpfomo' ) );
			}
		}

		/**
		 * Render admin notices.
		 *
		 * @since 2.0
		 *
		 * @param string $msg
		 * @param string $type
		 *
		 * @return void
		 */
		public static function render_notice( $msg, $type = 'success' ) {
			self::$notice_msg  = $msg;
			self::$notice_type = $type;

			add_action(
				'admin_notices',
				function() {
					$screen = get_current_screen();

					if ( ! in_array( $screen->id, array( 'ibx_wpfomo', 'edit-ibx_wpfomo' ) ) ) {
						return;
					}
					echo '<div class="notice notice-' . self::$notice_type . ' is-dismissible">';
					echo '<p>' . self::$notice_msg . '</p>';
					echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>';
					echo '</div>';
				}
			);
		}
	}
} // End if().

IBX_WPFomo_Loader::init();
