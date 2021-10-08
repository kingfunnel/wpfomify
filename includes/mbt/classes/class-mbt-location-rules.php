<?php

class MetaBox_Tabs_Location_Rules {
	static public function locations( $type = 'global' ) {
		$locations = array(
			'is_front_page'  => __( 'Front page', 'mbt' ),
			'is_home'        => __( 'Blog page', 'mbt' ),
			'is_singular'    => __( 'All posts, pages and custom post types', 'mbt' ),
			'is_single'      => __( 'All posts', 'mbt' ),
			'is_page'        => __( 'All pages', 'mbt' ),
			'is_attachment'  => __( 'All attachments', 'mbt' ),
			'is_search'      => __( 'Search results', 'mbt' ),
			'is_404'         => __( '404 error page', 'mbt' ),
			'is_archive'     => __( 'All archives', 'mbt' ),
			'is_category'    => __( 'All category archives', 'mbt' ),
			'is_tag'         => __( 'All tag archives', 'mbt' ),
		);

		if ( 'global' == $type ) {
			return $locations;
		}

		$locations = array();
		$post_types = MetaBox_Tabs_Helper::post_types();
		$taxonomies = MetaBox_Tabs_Helper::taxonomies();

		if ( ! empty( $post_types ) ) {

			unset( $post_types['post'] );
			unset( $post_types['page'] );

			foreach ( $post_types as $slug => $type ) {

				// translators: %s is for post type label.
				$locations[ 'is_singular-' . $slug ] = sprintf( __( 'All %s posts', 'mbt' ), '<strong>' . $type->label . '</strong>' );

				if ( $type->has_archive ) {
					// translators: %s is for post type label.
					$locations[ 'is_archive-' . $slug ] = sprintf( __( 'All %s archives', 'mbt' ), '<strong>' . $type->label . '</strong>' );
				}
			}

			foreach ( $taxonomies as $slug => $tax ) {
				// translators: %s is for taxonomy label.
				$locations[ 'is_tax-' . $slug ] = sprintf( __( 'All %s taxonomy archives', 'mbt' ), '<strong>' . $tax->label . '</strong>' );
			}
		}

		return $locations;
	}

	static public function check_location( $locations = array() ) {
		if ( empty( $locations ) ) {
			return true;
		}

		$status = array(
			'is_front_page'  => is_front_page(),
			'is_home'        => is_home(),
			'is_singular'    => is_singular(),
			'is_single'      => is_singular( 'post' ),
			'is_page'        => ( is_page() && ! is_front_page() ),
			'is_attachment'  => is_attachment(),
			'is_search'      => is_search(),
			'is_404'         => is_404(),
			'is_archive'     => is_archive(),
			'is_category'    => is_category(),
			'is_tag'         => is_tag(),
		);

		$post_types = MetaBox_Tabs_Helper::post_types();
		$taxonomies = MetaBox_Tabs_Helper::taxonomies();

		if ( ! empty( $post_types ) ) {

			unset( $post_types['post'] );
			unset( $post_types['page'] );

			foreach ( $post_types as $slug => $type ) {

				$status[ 'is_singular-' . $slug ] = is_singular( $slug );

				if ( $type->has_archive ) {
					$locations[ 'is_archive-' . $slug ] = is_post_type_archive( $slug );
				}
			}

			foreach ( $taxonomies as $slug => $tax ) {
				$locations[ 'is_tax-' . $slug ] = is_tax( $slug );
			}
		}

		$status_flag = false;

		foreach ( $locations as $location ) {
			if ( ! isset( $status[ $location ] ) || ! $status[ $location ] ) {
				continue;
			} else {
				$status_flag = true;
			}
		}

		return $status_flag;
	}

	static public function check_url( $urls ) {

		$urls = trim( $urls );

		if ( empty( $urls ) ) {
			return true;
		}

		if ( self::match_path( $urls ) ) {
			return true;
		}

		return false;
	}

	static public function match_path( $patterns ) {
		$patterns_safe = array();

		// Get the request URI from WP
		list($url_request) = explode( '?', $_SERVER['REQUEST_URI'] ); //$wp->request;
		$url_request = ltrim( trim( $url_request ), '/' );

		// Append the query string
		if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
			$url_request .= '?' . $_SERVER['QUERY_STRING'];
		} else {
			$url_request = trim( $url_request, '/' );
		}

		$rows = explode( "\n", $patterns );

		foreach ( $rows as $pattern ) {

			// Trim trailing, leading slashes and whitespace
			$pattern = trim( trim( $pattern ), '/' );

			// Escape regex chars
			$pattern = preg_quote( $pattern, '/' );

			// Enable wildcard checks
			$pattern = str_replace( '\*', '.*', $pattern );

			$patterns_safe[] = $pattern;

		}

		// Remove empty patterns
		$patterns_safe = array_filter( $patterns_safe );

		$regexps = sprintf( '/^(%s)$/i', implode( '|', $patterns_safe ) );

		return preg_match( $regexps, $url_request );

	}
}
