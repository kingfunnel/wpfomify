<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for helper functions.
 *
 * @since 1.0.0
 */
class IBX_WPFomo_Helper {
	protected static $timezone = null;

	protected static $location_data = false;

	/**
	 * Get parsed template HTML.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $template Template can be array or string.
	 * @param array $tags Array of merge tags or variables.
	 *
	 * @return string
	 */
	public static function get_notification_template( $template, $tags ) {
		return self::parse_notification_template( $template, $tags );
	}

	/**
	 * Replaces tags with their values in template.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $template Template can be array or string.
	 * @param array $tags Array of merge tags or variables.
	 *
	 * @return string
	 */
	public static function parse_notification_template( $template, $tags ) {
		$html = $template;

		// If template is in array format, lets break it down and
		// make HTML markup.
		if ( is_array( $template ) ) {
			$html = '';
			$css = array();

			if ( isset( $template['_css'] ) ) {
				$css = $template['_css'];
				unset( $template['_css'] );
			}

			for ( $i = 0; $i < count( $template ); $i++ ) {
				$style = isset( $css[ $i ] ) ? $css[ $i ] : '';

				if ( 0 == $i ) { // Line 1
					$html .= '<span class="ibx-notification-row-first" style="' . $style . '">' . $template[ $i ] . '</span>';
				}
				if ( 1 == $i ) { // Line 2
					$html .= '<span class="ibx-notification-popup-title ibx-notification-row-second" style="' . $style . '">' . $template[ $i ] . '</span>';
				}
				if ( 2 == $i ) { // Line 3
					$html .= '<span class="ibx-notification-row-third" style="' . $style . '">' . $template[ $i ] . '</span>';
				}
			}
		}

		// Get all merge tags from the template html.
		preg_match_all( '/{{([^}]*)}}/', $html, $tags_in_html, PREG_PATTERN_ORDER );

		// Holds the original tags without formatting parameteres.
		$actual_tags = array();

		// Holds the tags with formatting parameteres.
		$formatted_tags = array();

		if ( ! empty( $tags_in_html ) ) {
			for ( $i = 0; $i < count( $tags_in_html[1] ); $i++ ) {

				$x               = explode( '|', $tags_in_html[1][ $i ] );
				$tag_in_template = '{{' . trim( $tags_in_html[1][ $i ] ) . '}}';

				if ( is_array( $x ) ) {
					$actual_tag = '{{' . trim( $x[0] ) . '}}';
					if ( ! isset( $x[1] ) ) {
						$x[1] = ' ';
					}
					$actual_tags[ $actual_tag ]    = trim( $x[1] );
					$formatted_tags[ $actual_tag ] = $tag_in_template;
				} else {
					$actual_tags[ $tag_in_template ]    = '';
					$formatted_tags[ $tag_in_template ] = $tag_in_template;
				}
			}
		}

		// Loop through tags and convert the values in their relevant HTML.
		foreach ( $tags as $tag => $value ) {

			if ( isset( $actual_tags[ $tag ] ) ) {

				$variable        = explode( ':', $actual_tags[ $tag ] );
				$formatted_value = $value;

				switch ( trim( $variable[0] ) ) {
					case 'bold':
						$formatted_value = '<strong>' . $value . '</strong>';
						break;
					case 'italic':
						$formatted_value = '<em>' . $value . '</em>';
						break;
					case 'color':
						$formatted_value = '<span style="color: ' . trim( $variable[1] ) . ';">' . $value . '</span>';
						break;
					case 'bold+color':
						$formatted_value = '<strong style="color: ' . trim( $variable[1] ) . ';">' . $value . '</strong>';
						break;
					case 'italic+color':
						$formatted_value = '<em style="color: ' . trim( $variable[1] ) . ';">' . $value . '</em>';
						break;
					case 'propercase':
						$formatted_value = '<span style="text-transform: capitalize;">' . $value . '</span>';
						break;
					case 'upcase':
						$formatted_value = '<span style="text-transform: uppercase;">' . $value . '</span>';
						break;
					case 'downcase':
						$formatted_value = '<span style="text-transform: lowercase;">' . $value . '</span>';
						break;
					case 'fallback':
						$tmp_val         = trim( $variable[1] );
						$tmp_val         = str_replace( '[', '', $tmp_val );
						$tmp_val         = str_replace( ']', '', $tmp_val );
						$formatted_value = empty( $value ) ? $tmp_val : $value;
						break;
					default:
						break;
				}
				$html = str_replace( $formatted_tags[ $tag ], $formatted_value, $html );
			} else {
				if ( ! is_array( $html ) && ! is_array( $value ) ) {
					$html = str_replace( $tag, $value, $html );
				}
			} // End if().
		} // End foreach().

		$html = str_replace( '\\', '', $html );

		return $html;
	}

	public static function parse_notification_css( $css = array() ) {
		if ( empty( $css ) ) {
			return array();
		}

		$css = explode( ';', $css );
		$parsed_css = array();

		for ( $i = 0; $i < count( $css ); $i++ ) {
			if ( empty( $css[ $i ] ) ) {
				continue;
			}

			$props = explode( ':', $css[ $i ] );
			$prop = trim( $props[0] );
			$value = trim( $props[1] );

			$parsed_css[ $prop ] = $value;
		}

		return $parsed_css;
	}

	public static function stringify_notification_css( $css = array() ) {
		if ( empty( $css ) ) {
			return '';
		}

		$css_arr = array();

		foreach ( $css as $prop => $value ) {
			$css_arr[] = "$prop: $value;";
		}

		return implode( ' ', $css_arr );
	}

	public static function get_timezone() {
		if ( empty( self::$timezone ) ) {
			$timezone = get_option( 'timezone_string' );
			$utc_offset = get_option( 'gmt_offset', 0 );
			if ( $timezone ) {
				self::$timezone = $timezone;
			} elseif ( 0 === $utc_offset ) {
				self::$timezone = 'UTC';
			} else {
				$utc_offset *= 3600;
				if ( $timezone = timezone_name_from_abbr('', $utc_offset, 0 ) ) {
					self::$timezone = $timezone;
				} else {
					$is_dst = date( 'I' );
					foreach ( timezone_abbreviations_list() as $abbr ) {
						foreach ( $abbr as $city ) {
							if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
								self::$timezone = $city['timezone_id'];
								return;
							}
						}
					}
				}
			}
		}

		self::$timezone = ! empty( self::$timezone ) ? self::$timezone : 'UTC';

		return self::$timezone;
	}

	/**
	 * Get time HTML for notifications.
	 *
	 * @since 1.0
	 *
	 * @uses human_time_diff to convert time in human readable format.
	 *
	 * @return string
	 */
	public static function get_timeago_html( $time = false ) {
		if ( ! $time ) {
			return;
		}

		$timezone = self::get_timezone();
		$timezone_obj = new DateTimeZone( $timezone );
		$date = new DateTime( $time, $timezone_obj );
		$time = $date->format( 'c' );

		if ( ! $time || ( strtotime( $time ) - time() ) > 0 ) {
			return false;
		}

		$time = human_time_diff( strtotime( $time ), time() );
		$ago  = self::get_ago_translation();

		if ( ! empty( $time ) ) {
			$time_array = array(
				$time,
				$ago
			);

			/**
			 * Allow developers to translate time or change the order.
			 * 
			 * @since 2.2.0
			 * 
			 * @param array $time_array Array( [0] => time numeric value, [1] => hours, days, months, etc. )
			 */
			$time = implode( ' ', apply_filters( 'ibx_wpfomo_time_array', $time_array ) );
		}

		// translators: %1$s is for time "About 1 hour" and %2$s is for "ago" text.
		$time_ago = sprintf( __( '%1$s %2$s', 'ibx-wpfomo' ), esc_html( $time ), $ago );

		return $time;
	}

	/**
	 * Get translated version of "Someone" from settings.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_someone_translation() {
		// Get field value from settings.
		$text = IBX_WPFomo_Admin::get_settings( 'translate_someone' );

		if ( empty( $text ) ) {
			return __( 'Someone', 'ibx-wpfomo' );
		}

		return esc_attr( $text );
	}

	/**
	 * Get translated version of "ago" from settings.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public static function get_ago_translation() {
		// Get field value from settings.
		$text = IBX_WPFomo_Admin::get_settings( 'translate_ago' );
		if ( ! $text || empty( $text ) ) {
			return esc_html__( 'ago', 'ibx-wpfomo' );
		} elseif ( '-' == $text ) {
			return '';
		}

		return esc_attr( $text );
	}

	/**
	 * Fetch image from Gravatar.
	 *
	 * @since 1.0.0
	 *
	 * @param string $email
	 * @param string $default_img
	 *
	 * @return string
	 */
	public static function get_gravatar_image( $email = '', $default_img = '' ) {
		if ( ! empty( $email ) ) {
			$email        = strtolower( trim( $email ) );
			$gravtar_md5  = md5( $email );
			$gravatar_img = 'https://www.gravatar.com/avatar/' . $gravtar_md5;

			if ( ! empty( $default_img ) ) {
				$gravatar_img .= '?d=' . urlencode( $default_img );
			}

			return $gravatar_img;
		}

		return $default_img;
	}

	/**
	 * Get rating stars html.
	 *
	 * @since 1.0.0
	 *
	 * @param int $rating
	 *
	 * @return string
	 */
	public static function get_rating_stars( $rating = '' ) {
		if ( ! $rating || empty( $rating ) ) {
			return;
		}

		$stars = '';

		for ( $i = 0; $i < $rating; $i++ ) {
			$stars .= '<span>&#9734</span>';
		}

		return $stars;
	}

	/**
	 * Get fomo types.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 *
	 * @return string|array
	 */
	public static function get_notification_types( $type = '' ) {
		$types = apply_filters(
			'ibx_wpfomo_types',
			array(
				'fomo_bar'   => __( 'Notification Bar', 'ibx-wpfomo' ),
				'conversion' => __( 'Conversions Notification', 'ibx-wpfomo' ),
				'reviews'    => __( 'Reviews Notification', 'ibx-wpfomo' ),
			)
		);

		if ( isset( $types[ $type ] ) ) {
			return $types[ $type ];
		}

		return $types;
	}

	public static function get_default_conversion_source() {
		$source = 'custom';

		if ( class_exists( 'WooCommerce' ) ) {
			$source = 'woocommerce';
		} elseif ( class_exists( 'Easy_Digital_Downloads' ) ) {
			$source = 'edd';
		} elseif ( class_exists( 'Give' ) ) {
			$source = 'give';
		} elseif ( class_exists( 'GFForms' ) ) {
			$source = 'gravity-forms';
		} else {
			$source = 'custom';
		}

		return $source;
	}

	public static function get_default_reviews_source() {
		$source = 'custom';

		if ( class_exists( 'WooCommerce' ) ) {
			$source = 'woocommerce';
		} else {
			$source = 'custom';
		}

		return $source;
	}
	/**
	 * Renders CSS box-shadow by using the variables.
	 *
	 * @since 1.0.0
	 *
	 * @param string $horizontal
	 * @param string $vertical
	 * @param string $blur
	 * @param string $spread
	 * @param string $color
	 *
	 * @return string
	 */
	public static function render_box_shadow_css( $horizontal = '0px', $vertical = '0px', $blur = '0px', $spread = '0px', $color = '#666' ) {
		ob_start();
		?>
		-webkit-box-shadow: <?php echo $horizontal; ?> <?php echo $vertical; ?> <?php echo $blur; ?> <?php echo $spread; ?> <?php echo $color; ?>;
		-moz-box-shadow: <?php echo $horizontal; ?> <?php echo $vertical; ?> <?php echo $blur; ?> <?php echo $spread; ?> <?php echo $color; ?>;
		-o-box-shadow: <?php echo $horizontal; ?> <?php echo $vertical; ?> <?php echo $blur; ?> <?php echo $spread; ?> <?php echo $color; ?>;
		box-shadow: <?php echo $horizontal; ?> <?php echo $vertical; ?> <?php echo $blur; ?> <?php echo $spread; ?> <?php echo $color; ?>;
		<?php

		return ob_get_clean();
	}

	/**
	 * Convert hex color value to RGBA.
	 *
	 * @since 1.0.0
	 *
	 * @param string        $hex
	 * @param integer|float $opacity
	 *
	 * @return string
	 */
	public static function hex2rgba( $hex, $opacity ) {
		 $hex = str_replace( '#', '', $hex );

		if ( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		$rgba = array( $r, $g, $b, $opacity );

		return 'rgba(' . implode( ', ', $rgba ) . ')';
	}

	/**
	 * Curl get content.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function curl_get_contents( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' );
		$html = curl_exec( $ch );
		$data = curl_exec( $ch );
		curl_close( $ch );
		return $data;
	}

	/**
	 * Generate API key.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public static function api_key() {
		return md5( home_url() );
	}

	public static function is_pro_version() {
		return ! defined( 'IBX_WPFOMO_LITE' );
	}

	/**
	 * Displayes saved metadata for a meta key of the post.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public static function display_saved_log( $key ) {
		if ( isset( $_GET['post'] ) && isset( $_GET['saved_log'] ) ) {
			$post_id = $_GET['post'];
			echo '<div class="notice notice-warning"><pre>';
			print_r( IBX_WPFomo_Admin::get_post_meta( $post_id, $key ) );
			echo '</pre></div>';
		}
	}

	public static function get_client_ip() {
		$ipaddress = '';
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} elseif ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 'UNKNOWN';
		}

		if ( ! empty( $ipaddress ) && 'UNKNOWN' != $ipaddress ) {
			$ipaddress = explode( ',', $ipaddress );
			if ( is_array( $ipaddress ) ) {
				$ipaddress = trim( $ipaddress[0] );
			}
		}
		return $ipaddress;
	}

	/**
	 * Get client location from IP address.
	 *
	 * @since 2.0
	 * @param string $ip_address
	 * @return array
	 */
	public static function get_location_from_ip( $ip_address ) {
		$location = array(
			'city'	=> '',
			'state'	=> '',
			'country'	=> '',
		);

		$has_cached_data = false;

		if ( ! empty( $ip_address ) ) {
			if ( ! self::$location_data ) {
				self::$location_data = get_transient( '_ibx_wpfomo_ip_location_data' );
			}

			if ( ! empty( self::$location_data ) ) {
				$location_data = maybe_unserialize( self::$location_data );
				if ( isset( $location_data[ $ip_address ] ) ) {
					return $location_data[ $ip_address ];
				}
			} else {
				self::$location_data = array();
			}

			$geo_plugin_url = 'http://www.geoplugin.net/php.gp?ip=' . $ip_address;

			$botRegexPattern = "(googlebot\/|Googlebot\-Mobile|Googlebot\-Image|Google favicon|Mediapartners\-Google|bingbot|slurp|java|wget|curl|Commons\-HttpClient|Python\-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|jyxobot|FAST\-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub\.org|UsineNouvelleCrawler|antibot|netresearchserver|speedy|fluffy|bibnum\.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|IOI|ips\-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail\.RU_Bot|discobot|heritrix|findthatfile|europarchive\.org|NerdByNature\.Bot|sistrix crawler|ahrefsbot|Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb\-spider|sogou|lssbot|careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|openindexspider|gnam gnam spider|web\-archive\-net\.com\.bot|backlinkcrawler|coccoc|integromedb|content crawler spider|toplistbot|seokicks\-robot|it2media\-domain\-crawler|ip\-web\-crawler\.com|siteexplorer\.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki\-bot|CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|g00g1e\.net|GrapeshotCrawler|urlappendbot|brainobot|fr\-crawler|binlar|SimpleCrawler|Livelapbot|Twitterbot|cXensebot|smtbot|bnf\.fr_bot|A6\-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive\.org_bot|Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j\-asr|Domain Re\-Animator Bot|AddThis|YisouSpider|BLEXBot|YandexBot|SurdotlyBot|AwarioRssBot|FeedlyBot|Barkrowler|Gluten Free Crawler|Cliqzbot)";
 
    		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && ! preg_match("/{$botRegexPattern}/", $_SERVER['HTTP_USER_AGENT']) ) {
				$response = maybe_unserialize( @file_get_contents( $geo_plugin_url ) );
				if ( ! empty( $response ) ) {
					$location['city']    = $response['geoplugin_city'];
					$location['state']  = $response['geoplugin_regionName'];
					$location['country'] = $response['geoplugin_countryName'];

					self::$location_data[ $ip_address ] = $location;
					set_transient( '_ibx_wpfomo_ip_location_data', maybe_serialize( self::$location_data ), HOUR_IN_SECONDS );
				}
			}
		}
		return $location;
	}

	/**
	 * Set transient for any notice.
	 */
	static public function set_notice( $message, $type ) {
		delete_transient( 'ibx_wpfomo_post_notice' );
		set_transient(
			'ibx_wpfomo_post_notice',
			array(
				'type'		=> $type,
				'message'	=> $message,
			),
			( 5 / 60 ) * HOUR_IN_SECONDS
		);
	}

	/**
	 * Set notice value from transient.
	 */
	static public function get_notice() {
		$notice = get_transient( 'ibx_wpfomo_post_notice' );
		$output = '';

		if ( $notice ) {
			ob_start();
			?>
			<div class="notice notice-<?php echo $notice['type']; ?>">
				<p><?php echo $notice['message']; ?></p>
			</div>
			<?php
			$output = ob_get_clean();

			delete_transient( 'ibx_wpfomo_post_notice' );
		}

		return $output;
	}

	/**
	 * Store data in transient for the given time in plugin options.
	 */
	static public function set_cache_data( $key, $data ) {
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		delete_transient( $key );
		set_transient( $key, maybe_serialize( $data ), ( $cache_duration / 60 ) * HOUR_IN_SECONDS );
	}

	static public function get_cache_data( $key = '' ) {
		if ( empty( $key ) ) {
			return false;
		}

		// get data from transient and unserialize it.
		$cached_data = maybe_unserialize( get_transient( $key ) );

		// return data if exist in transient.
		if ( is_array( $cached_data ) && ! empty( $cached_data ) ) {
			return $cached_data;
		}

		return false;
	}

	static public function get_notification_data( $ids = array() ) {
		if ( empty( $ids ) ) {
			return array();
		}

		$data = array();

		foreach ( $ids as $id ) {
			$settings       = MetaBox_Tabs::get_metabox_settings( $id );
			$data['config'] = array(
				'id'               => $id,
				'initial_delay'    => ! empty( $settings->initial_delay ) ? $settings->initial_delay * 1000 : 0,
				'display_duration' => ! empty( $settings->display_time ) ? $settings->display_time * 1000 : 0,
				'delay_each'       => ! empty( $settings->delay_between ) ? $settings->delay_between * 1000 : 0,
				'loop'             => absint( $settings->loop ),
				'randomize'        => isset( $settings->randomize ) ? absint( $settings->randomize ) : 0,
			);

			if ( 'conversion' === $settings->type ) {
				$data['config']['source'] = $settings->conversions_source;
			}

			ob_start();
			include IBX_WPFOMO_DIR . 'includes/frontend-notification.php';
			$content = ob_get_clean();

			$data['content'] = htmlspecialchars_decode( $content );
		}

		return $data;
	}
}
