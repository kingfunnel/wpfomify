<?php

// Helper class.
class MetaBox_Tabs_Helper {
	/**
	 * Returns an array of posts.
	 *
	 * @since 1.0.0
	 * @param array $options Arguments.
	 * @return array
	 */
	static public function posts( $options = array() ) {
		if ( ! is_array( $options ) || empty( $options ) ) {
			return array();
		}
		if ( ! isset( $options['post_type'] ) || empty( $options['post_type'] ) ) {
			return array();
		}

		$post_type  = $options['post_type'];
		$exclude    = ( isset( $options['exclude'] ) && is_array( $options['exclude'] ) ) ? $options['exclude'] : array();
		$limit      = ( isset( $options['limit'] ) && ! empty( $options['limit'] ) ) ? $options['limit'] : '-1';
		$taxonomies = ( isset( $options['taxonomies'] ) && is_array( $options['taxonomies'] ) ) ? $options['taxonomies'] : array();

		$args = array(
			'post_type'         => $post_type,
			'post_status'       => 'publish',
			'posts_per_page'    => $limit,
		);

		if ( count( $exclude ) ) {
			$args['posts__not_in'] = $exclude;
		}

		if ( count( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy ) {
				if ( ! is_array( $taxonomy ) ) {
					continue;
				}
				$args['tax_query'][] = $taxonomy;
			}
		}

		$posts = get_posts( $args );

		return $posts;
	}

	/**
	 * Returns an array of data for post types.
	 *
	 * @since 1.0.0
	 * @param array $exclude Post types to be excluded.
	 * @return array
	 */
	static public function post_types( $exclude = array() ) {
		$post_types = get_post_types(array(
			'public'	=> true,
			'show_ui'	=> true,
		), 'objects');

		unset( $post_types['attachment'] );

		if ( count( $exclude ) ) {
			foreach ( $exclude as $type ) {
				if ( isset( $post_types[ $type ] ) ) {
					unset( $post_types[ $type ] );
				}
			}
		}

		return $post_types;
	}

	/**
	 * Get an array of supported taxonomy data for a post type.
	 *
	 * @since 1.0.0
	 * @param string $post_type The post type to get taxonomies for.
	 * @param array $exclude Taxonomies to be excluded.
	 * @return array
	 */
	static public function taxonomies( $post_type = '', $exclude = array() ) {
		if ( empty( $post_type ) ) {
			$taxonomies = get_taxonomies(
				array(
					'public'       => true,
					'_builtin'     => false,
				),
				'objects'
			);
		} else {
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		}

		$data = array();

		foreach ( $taxonomies as $tax_slug => $tax ) {

			if ( ! $tax->public || ! $tax->show_ui ) {
				continue;
			}

			if ( in_array( $tax_slug, $exclude ) ) {
				continue;
			}

			$data[ $tax_slug ] = $tax;
		}

		return apply_filters( 'mbt_post_loop_taxonomies', $data, $taxonomies, $post_type );
	}

	/**
	 * Get an array of data for suggest field.
	 *
	 * @since 1.0.0
	 * @param string $action Action to be called to retrieve the data.
	 * @param array $options Arguments to be passed in callback function.
	 * @return array
	 */
	static public function get_suggest_data( $action = '', $options = array() ) {
		if ( 'none' == $action && ! empty( $options ) ) {
			return $options;
		}

		if ( empty( $action ) ) {
			return;
		}

		$data = array();

		switch ( $action ) {
			case 'get_posts':
				if ( ! empty( $options ) ) {
					$posts = self::posts( $options );
					if ( ! empty( $posts ) ) {
						foreach ( $posts as $post ) {
							$data[ $post->ID ] = $post->post_title;
						}
					}
				}
				break;
			case 'get_taxonomies':
				if ( ! empty( $options ) ) {
					$post_type  = isset( $options['post_type'] ) ? $options['post_type'] : '';
					$exclude    = isset( $options['exclude'] ) ? $options['post_type'] : array();
					$taxonomies = self::taxonomies( $post_type, $exclude );
					if ( ! empty( $taxonomies ) ) {
						foreach ( $taxonomies as $slug => $tax ) {
							$data[ $slug ] = $tax->label;
						}
					}
				}
				break;
			case 'get_post_types':
				if ( ! empty( $options ) ) {
					$exclude    = isset( $options['exclude'] ) ? $options['post_type'] : array();
					$post_types = self::post_types( $exclude );
					if ( ! empty( $post_types ) ) {
						foreach ( $post_types as $slug => $type ) {
							$data[ $slug ] = $type->labels->name;
						}
					}
				}
				break;
			case 'get_locations':
				$type = ( ! empty( $options ) && isset( $options['type'] ) ) ? $options['type'] : '';
				$data = MetaBox_Tabs_Location_Rules::locations( $type );
				break;

			default:
				break;
		} // End switch().

		return apply_filters( 'mbt_filter_suggest_field_data', $data, $action );
	}
}
