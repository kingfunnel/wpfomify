<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper logic for primary class.
 * @since 2.1
 */
class IBX_WPFomo_WordPress_Helper {
	/**
	 * Get plugin information from WordPress repo.
	 */
	static public function get_plugin_info( $slug ) {
		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin-install.php';
		}

		$info = plugins_api(
			'plugin_information',
			array(
				'slug' => $slug,
				'fields' => array(
					'downloaded' => true,
					'icons' => true,
					'historical_summary' => true,
					'active_installs' => true,
				),
			)
		);

		if ( is_wp_error( $info ) ) {
			self::set_notice( $info->get_error_message(), 'error' );
			return array();
		}

		$data = array();
		$keys = array(
			'name',
			'slug',
			'num_ratings',
			'rating',
			'homepage',
			'version',
			'downloaded',
			'icons',
			'active_installs',
			'author_profile',
			'author',
		);

		foreach ( $keys as $key ) {
			if ( isset( $info->{$key} ) ) {
				$data[ $key ] = $info->{$key};
				if ( 'slug' === $key ) {
					$data['link'] = 'https://wordpress.org/plugins/' . $info->{$key};
				}
			}
		}

		if ( isset( $info->sections ) && isset( $info->sections['reviews'] ) ) {
			$data['reviews'] = self::get_parsed_reviews( $info->sections['reviews'] );
		}

		$stats = wp_remote_get(
			'https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug=' . $data['slug'] . '&historical_summary=1',
			array(
				'timeout' => 120,
				'httpversion' => '1.1',
			)
		);

		if ( ! is_wp_error( $stats ) ) {
			$stats = json_decode( wp_remote_retrieve_body( $stats ), ARRAY_A );
			$data['downloads'] = $stats;
		} else {
			self::set_notice(
				// translators: %s is for WP error message.
				sprintf( __( 'Unable to fetch download statistics at the moment: %s', 'ibx-wpfomo' ), $stats->get_error_message() ),
				'error'
			);
		}

		return $data;
	}

	/**
	 * Get theme information from WordPress repo.
	 */
	static public function get_theme_info( $slug ) {
		if ( ! function_exists( 'themes_api' ) ) {
			require_once ABSPATH . '/wp-admin/includes/theme.php';
		}

		$info = themes_api(
			'theme_information',
			array(
				'slug' => $slug,
				'fields' => array(
					'downloaded' => true,
					'sections' => true,
					'theme_url' => true,
					'photon_screenshots' => true,
					'screenshot_url' => true,
					'active_installs' => true,
				),
			)
		);

		if ( is_wp_error( $info ) ) {
			self::set_notice( $info->get_error_message(), 'error' );
			return array();
		}

		$data = array();
		$keys = array(
			'name',
			'slug',
			'num_ratings',
			'rating',
			'homepage',
			'version',
			'downloaded',
			'screenshot_url',
			'active_installs',
			'author_profile',
			'author',
			'sections',
		);

		foreach ( $keys as $key ) {
			if ( isset( $info->{$key} ) ) {
				$data[ $key ] = $info->{$key};
			}
		}

		$stats = wp_remote_get(
			'https://api.wordpress.org/stats/themes/1.0/downloads.php?slug=' . $data['slug'] . '&historical_summary=1',
			array(
				'timeout' => 120,
				'httpversion' => '1.1',
			)
		);

		if ( ! is_wp_error( $stats ) ) {
			$stats = json_decode( wp_remote_retrieve_body( $stats ), ARRAY_A );
			$data['downloads'] = $stats;
		} else {
			self::set_notice(
				// translators: %s is for WP error message.
				sprintf( __( 'Unable to fetch download statistics at the moment: %s', 'ibx-wpfomo' ), $stats->get_error_message() ),
				'error'
			);
		}

		return $data;
	}

	/**
	 * Parse reviews from HTML content.
	 */
	static public function get_parsed_reviews( $reviews_html ) {
		$reviews = new IBX_WPFomo_WordPress_Reviews( $reviews_html );
		return $reviews->get_reviews();
	}

	static public function format_number( $n ) {
		$n = (int) str_replace( ',', '', $n );
		if ( $n > 0 && $n < 1000 ) {
			// 1 - 999
			$n_format = floor( $n );
			$suffix = '';
		} elseif ( $n >= 1000 && $n < 1000000 ) {
			// 1k-999k
			$n_format = floor( $n / 1000 );
			$suffix = 'K+';
		} elseif ( $n >= 1000000 && $n < 1000000000 ) {
			// 1m-999m
			$n_format = floor( $n / 1000000 );
			$suffix = 'M+';
		} elseif ( $n >= 1000000000 && $n < 1000000000000 ) {
			// 1b-999b
			$n_format = floor( $n / 1000000000 );
			$suffix = 'B+';
		} elseif ( $n >= 1000000000000 ) {
			// 1t+
			$n_format = floor( $n / 1000000000000 );
			$suffix = 'T+';
		}

		return ! empty( $n_format . $suffix ) ? $n_format . $suffix : 0;
	}

	/**
	 * Set transient for any notice.
	 */
	static public function set_notice( $message, $type ) {
		delete_transient( 'ibx_wpfomo_wprepo_notice' );
		set_transient(
			'ibx_wpfomo_wprepo_notice',
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
		$notice = get_transient( 'ibx_wpfomo_wprepo_notice' );
		$output = '';

		if ( $notice ) {
			ob_start();
			?>
			<div class="notice notice-<?php echo $notice['type']; ?>">
				<p><?php echo $notice['message']; ?></p>
			</div>
			<?php
			$output = ob_get_clean();

			delete_transient( 'ibx_wpfomo_wprepo_notice' );
		}

		return $output;
	}
}
