<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'IBX_WPFomo_Frontend' ) ) {
	/**
	 * Handles all logices and frontend configuration.
	 *
	 * @since 1.0.0
	 */
	class IBX_WPFomo_Frontend {

		public static $active = array();

		public static $is_cookie_set = 0;

		public $notifications_type = array();

		/**
		 * Primary class constructor.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function __construct() {
			$this->notifications_type = apply_filters( 'ibx_wpfomo_notifications_type', array( 'conversion', 'reviews' ) );

			$this->init_hooks();
		}

		/**
		 * Initialize hooks and filters.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function init_hooks() {
			add_action( 'init', array( $this, 'set_cookie' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'wp', array( $this, 'get_active_items' ) );
			add_action( 'wp_footer', array( $this, 'maybe_display_items' ) );
		}

		/**
		 * Set cookie.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function set_cookie() {
			if ( is_admin() || wp_doing_ajax() ) {
				return;
			}
			$cookie_ip = ( isset( $_COOKIE['ibx_wpfomo_ip'] ) ) ? $_COOKIE['ibx_wpfomo_ip'] : '';
			if ( empty( $cookie_ip ) ) {
				$current_ip = $this->get_client_ip();
				setcookie( 'ibx_wpfomo_ip', $current_ip, strtotime( '+1 month' ), '/' );
				self::$is_cookie_set = 1;
			}
		}

		/**
		 * Enqueue styles and scripts.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function load_scripts() {
			wp_enqueue_style( 'ibx-wpfomo-style', IBX_WPFOMO_URL . 'assets/css/frontend.css', array(), IBX_WPFOMO_VER );

			wp_enqueue_script( 'jquery-cookie-script', IBX_WPFOMO_URL . 'assets/js/jquery.cookie.js', array( 'jquery' ), '' );
			wp_enqueue_script( 'ibx-wpfomo-script', IBX_WPFOMO_URL . 'assets/js/frontend.js', array( 'jquery', 'jquery-cookie-script' ), IBX_WPFOMO_VER, true );
		}

		/**
		 * Get all active notifications.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function get_active_items() {
			// WP Query arguments.
			$args = array(
				'post_type'         => 'ibx_wpfomo',
				'posts_per_page'    => '-1',
				'post_status'		=> 'publish',
				'meta_query'        => array(
					array(
						'key'           => 'ibx_wpfomo_active_check',
						'value'         => '1',
						'compare'       => '=',
					),
				),
			);

			// Get the notification posts.
			$posts = get_posts( $args );

			if ( count( $posts ) ) {
				foreach ( $posts as $post ) {
					self::$active[] = $post->ID;
				}
			}
		}

		/**
		 * Render the notification.
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function maybe_display_items() {
			// Return if there is no notification.
			if ( empty( self::$active ) ) {
				return;
			}

			$notifications_type = $this->notifications_type;

			// Conversions.
			$conversion_ids = array();

			// Reviews.
			$reviews_ids = array();

			// Custom Form Details.
			$custom_form_details = array();

			// Current post id.
			$post_id = get_the_ID();

			// Do check for page conditions, logged in, etc here.
			foreach ( self::$active as $id ) {
				$settings = MetaBox_Tabs::get_metabox_settings( $id );

				$logged_in = is_user_logged_in();
				$logged_in_meta = $settings->visibility_display;

				// Check logged in condition.
				if ( ( $logged_in && 'logged_out' == $logged_in_meta ) || ( ! $logged_in && 'logged_in' == $logged_in_meta ) ) {
					continue;
				}

				// check page location.
				$show_on            = $settings->show_on;
				$global_locations   = $settings->global_locations;
				$custom_locations   = isset( $settings->custom_locations ) ? $settings->custom_locations : array();
				$page_urls          = isset( $settings->page_urls ) ? $settings->page_urls : '';
				$rules_defined      = true;
				$global_location_check = true;
				$custom_location_check = true;
				$page_urls_check 	= true;

				if ( empty( $global_locations ) || ! is_array( $global_locations ) ) {
					if ( empty( $custom_locations ) || ! is_array( $custom_locations ) ) {
						if ( empty( $page_urls ) ) {
							$rules_defined = false;
						}
					}
				}

				// For custom form url.
				if ( 'custom_form_url' == $settings->conversions_source ) {
					$form_key = $settings->custom_form_select;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_src_post']        = $settings->custom_form_src_post;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_title']           = $settings->custom_form_title;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_conversion_id']   = $id;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_unique_key_attr'] = $settings->custom_form_unique_key_attr;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_select']          = $settings->custom_form_select;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_url']             = $settings->custom_form_url;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_name_select']     = $settings->custom_form_name_select;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_email_select']    = $settings->custom_form_email_select;
					$custom_form_details[ $settings->custom_form_src_post ][ $form_key ]['custom_form_notification_link']    = $settings->custom_form_target_url;
				}

				if ( 'selected' == $show_on ) {

					if ( ! empty( $global_locations ) && is_array( $global_locations ) ) {
						$global_location_check = MetaBox_Tabs_Location_Rules::check_location( $global_locations );
					} else {
						$global_location_check = false;
					}

					if ( ! empty( $custom_locations ) && is_array( $custom_locations ) ) {
						$custom_location_check = MetaBox_Tabs_Location_Rules::check_location( $custom_locations );
					} else {
						$custom_location_check = false;
					}

					if ( ! empty( $page_urls ) ) {
						$page_urls_check = MetaBox_Tabs_Location_Rules::check_url( $page_urls );
					} else {
						$page_urls_check = false;
					}

					// do not proceed further if none of these condition matches.
					if ( ! $global_location_check && ! $custom_location_check && ! $page_urls_check && $rules_defined ) {
						continue;
					}
				} elseif ( 'hide' == $show_on ) {

					if ( ! empty( $global_locations ) && is_array( $global_locations ) ) {
						$matched = MetaBox_Tabs_Location_Rules::check_location( $global_locations );
						if ( $matched ) {
							continue;
						}
					}

					if ( ! empty( $custom_locations ) && is_array( $custom_locations ) ) {
						$matched = MetaBox_Tabs_Location_Rules::check_location( $custom_locations );
						if ( $matched ) {
							continue;
						}
					}

					if ( ! empty( $page_urls ) && MetaBox_Tabs_Location_Rules::check_url( $page_urls ) ) {
						continue;
					}
				} // End if().

				$current_ip = $this->get_client_ip();
				$visitors   = $settings->visibility_visitors;
				$cookie_ip  = ( isset( $_COOKIE['ibx_wpfomo_ip'] ) ) ? $_COOKIE['ibx_wpfomo_ip'] : '';

				// New visitors.
				if ( 'new' == $visitors ) {
					if ( $cookie_ip == $current_ip ) {
						continue;
					}
				}

				switch ( $settings->type ) {
					case 'fomo_bar':
						require IBX_WPFOMO_DIR . 'includes/frontend-fomo-bar.php';
						break;
					case 'conversion':
						$conversion_ids[] = $id;
						break;
					case 'reviews':
						$reviews_ids[] = $id;
						break;
					default:
						break;
				}

				do_action( 'ibx_wpfomo_frontend_render_content', $settings->type, $settings );

				if ( in_array( $settings->type, $notifications_type ) ) : ?>
				<style id="ibx-notification-<?php echo $id; ?>-style">
					.ibx-notification-popup-<?php echo $id; ?> {
						<?php if ( $settings->background_color ) { ?>
							background: <?php echo $settings->background_color; ?>;
						<?php } ?>
						<?php if ( $settings->text_color ) { ?>
							color: <?php echo $settings->text_color; ?>;
						<?php } ?>
						<?php if ( $settings->round_corners >= 0 ) { ?>
							border-radius: <?php echo $settings->round_corners; ?>px;
						<?php } ?>
						<?php if ( $settings->border >= 0 ) { ?>
							border-width: <?php echo $settings->border; ?>px;
							border-style: solid;
							border-color: <?php echo $settings->border_color; ?>;
						<?php } ?>
						<?php
							$shadow_blur = ( $settings->shadow_blur >= 0 ) ? $settings->shadow_blur . 'px' : '0';
							$shadow_spread = ( $settings->shadow_spread >= 0 ) ? $settings->shadow_spread . 'px' : '0';
							$shadow_opacity = ! empty( $settings->shadow_opacity ) ? ( $settings->shadow_opacity / 100 ) : 1;
							$shadow_color = IBX_WPFomo_Helper::hex2rgba( $settings->shadow_color, $shadow_opacity );
						?>
						<?php echo IBX_WPFomo_Helper::render_box_shadow_css( '0', '0', $shadow_blur, $shadow_spread, $shadow_color ); ?>
					}
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-title,
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-review-name,
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-review-text {
						<?php if ( $settings->link_color ) { ?>
							color: <?php echo $settings->link_color; ?>;
						<?php } ?>
					}
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-rating span {
						<?php if ( $settings->star_color ) { ?>
							color: <?php echo $settings->star_color; ?>;
						<?php } ?>
					}
					.ibx-notification-popup--<?php echo $id; ?> .ibx-notification-popup-close {
						<?php if ( $settings->text_color ) { ?>
							color: <?php echo $settings->text_color; ?>;
						<?php } ?>
					}
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-img {
						<?php if ( isset( $settings->img_size ) && $settings->img_size > 0 ) { ?>
							height: <?php echo $settings->img_size; ?>px;
							width: <?php echo $settings->img_size; ?>px;
						<?php } ?>
					}
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-img img {
						<?php if ( $settings->img_round_corners >= 0 ) { ?>
							border-radius: <?php echo $settings->img_round_corners; ?>px;
						<?php } ?>
						<?php if ( isset( $settings->img_size ) && $settings->img_size > 0 ) { ?>
							max-height: <?php echo $settings->img_size; ?>px;
						<?php } ?>
					}
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-img.has-letter {
						<?php if ( $settings->img_round_corners >= 0 ) { ?>
							border-radius: <?php echo $settings->img_round_corners; ?>px;
						<?php } ?>
					}
					.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-text {
						<?php if ( isset( $settings->vertical_padding ) && $settings->vertical_padding >= 0 ) { ?>
							margin-top: <?php echo $settings->vertical_padding; ?>px;
							margin-bottom: <?php echo $settings->vertical_padding; ?>px;
						<?php } ?>
						<?php if ( isset( $settings->horizontal_padding ) && $settings->horizontal_padding >= 0 ) { ?>
							margin-left: <?php echo $settings->horizontal_padding; ?>px;
							margin-right: <?php echo $settings->horizontal_padding; ?>px;
						<?php } ?>
					}

					<?php if ( isset( $settings->first_row_font_size ) && ! empty( $settings->first_row_font_size ) ) { ?>
						.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-row-first {
							font-size: <?php echo $settings->first_row_font_size; ?>px;
						}
					<?php } ?>
					<?php if ( isset( $settings->second_row_font_size ) && ! empty( $settings->second_row_font_size ) ) { ?>
						.ibx-notification-popup-<?php echo $id; ?> .ibx-notification-popup-title.ibx-notification-row-second {
							font-size: <?php echo $settings->second_row_font_size; ?>px;
						}
					<?php } ?>
				</style>
				<?php endif;
			} // End foreach().


			$conversion_data = IBX_WPFomo_Helper::get_notification_data( $conversion_ids );
			$reviews_data = IBX_WPFomo_Helper::get_notification_data( $reviews_ids );
			?>
			<script type="text/javascript">
				var ibx_fomo = {
					nonce: '<?php echo wp_create_nonce( 'ibx_fomo_conversion_nonce_front' ); ?>',
					conversions: <?php echo json_encode( $conversion_ids ); ?>,
					reviews: <?php echo json_encode( $reviews_ids ); ?>,
					ajaxurl: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
					custom_form_details: <?php echo json_encode( $custom_form_details ); ?>,
					post_id: <?php echo empty( $post_id ) ? '0' : $post_id; ?>,
					form_classes: <?php echo json_encode( IBX_WPFomo_Form_Parser::$form_classes_array, true ); ?>,
					data: {
						conversions: <?php echo is_array( $conversion_data ) ? json_encode( $conversion_data, JSON_INVALID_UTF8_IGNORE ) : json_encode( array() ); ?>,
						reviews: <?php echo is_array( $reviews_data ) ? json_encode( $reviews_data, JSON_INVALID_UTF8_IGNORE ) : json_encode( array() ); ?>
					}
				};                    
			</script>
			<?php
		}

		/**
		 * Get IP address of the client.
		 *
		 * @since 1.0.0
		 * @return string
		 */
		public function get_client_ip() {
			return IBX_WPFomo_Helper::get_client_ip();
		}
	}

	$ibx_wpfomo_frontend = new IBX_WPFomo_Frontend();
} // End if().
