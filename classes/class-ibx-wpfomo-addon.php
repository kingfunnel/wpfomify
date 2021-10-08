<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class that gets extended by all add-on classes.
 *
 * @since 1.1
 */
abstract class IBX_WPFomo_Addon {
	/**
	 * Holds the class object.
	 *
	 * @since 1.1
	 * @var object
	 */
	public static $instance;

	/**
	 * Holds the error messages.
	 *
	 * @since 1.1
	 * @var array $errors
	 */
	public $errors = array();

	/**
	 * A display name for the addon.
	 *
	 * @since 1.1
	 * @var string $name
	 */
	public $name;

		/**
	 * A slug for the addon.
	 *
	 * @since 1.1
	 * @var string $slug
	 */
	public $slug;

	/**
	 * The addon's directory path.
	 *
	 * @since 1.1
	 * @var string $dir
	 */
	public $dir;

	/**
	 * The addon's directory url.
	 *
	 * @since 1.1
	 * @var string $url
	 */
	public $url;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.1
	 */
	public function __construct( $params = array() ) {
		if ( ! is_array( $params ) || empty( $params ) ) {
			return;
		}

		if ( in_array( $params['slug'], IBX_WPFomo_Addons::$addons ) ) {
			// translators: %s stands for the addon slug.
			$this->errors[] = sprintf( _x( 'An addon with the slug %s already exists! Please namespace your addon filenames to ensure compatibility with WPFomify.', '%s stands for the addon slug.', 'ibx-wpfomo' ), $params['slug'] );
		}

		$class_info             = new ReflectionClass( $this );
		$class_path             = $class_info->getFileName();
		$dir_path               = dirname( $class_path );
		$this->slug             = $params['slug'];
		$this->name             = $params['name'];

		// We need to normalize the paths here since path comparisons
		// break on Windows because they use backslashes.
		$abspath                    = str_replace( '\\', '/', ABSPATH );
		$wpfomo_dir                 = str_replace( '\\', '/', IBX_WPFOMO_DIR );
		$dir_path                   = str_replace( '\\', '/', $dir_path );
		$stylesheet_directory       = str_replace( '\\', '/', get_stylesheet_directory() );
		$stylesheet_directory_uri   = str_replace( '\\', '/', get_stylesheet_directory_uri() );
		$template_directory         = str_replace( '\\', '/', get_template_directory() );
		$template_directory_uri     = str_replace( '\\', '/', get_template_directory_uri() );

		// Find the right paths.
		if ( is_child_theme() && stristr( $dir_path, $stylesheet_directory ) ) {
			$this->url = trailingslashit( str_replace( $stylesheet_directory, $stylesheet_directory_uri, $dir_path ) );
			$this->dir = trailingslashit( $dir_path );
		} elseif ( stristr( $dir_path, $template_directory ) ) {
			$this->url = trailingslashit( str_replace( $template_directory, $template_directory_uri, $dir_path ) );
			$this->dir = trailingslashit( $dir_path );
		} elseif ( isset( $params['url'] ) && isset( $params['dir'] ) ) {
			$this->url = trailingslashit( $params['url'] );
			$this->dir = trailingslashit( $params['dir'] );
		} elseif ( ! stristr( $dir_path, $wpfomo_dir ) ) {
			$this->url = trailingslashit( str_replace( trailingslashit( $abspath ), trailingslashit( home_url() ), $dir_path ) );
			$this->dir = trailingslashit( $dir_path );
		}

		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.1
	 */
	private function init_hooks() {
		if ( is_array( $this->errors ) && ! empty( $this->errors ) ) {
			IBX_WPFomo_Admin::$errors = array_merge( IBX_WPFomo_Admin::$errors, $this->errors );
			return;
		}

		add_filter( 'ibx_wpfomo_admin_general_settings', array( $this, 'register_admin_settings' ), 10, 1 );
		add_action( 'ibx_wpfomo_before_metabox_load', array( $this, 'init_fields' ) );
		add_action( 'ibx_wpfomo_admin_meta_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'ibx_wpfomo_conversion_data', array( $this, 'add_conversion_data' ), 10, 2 );
		add_filter( 'ibx_wpfomo_reviews_data', array( $this, 'add_reviews_data' ), 10, 2 );
	}

	/**
	 * Init fields for integration.
	 *
	 * @since 1.1
	 * @return void
	 */
	public function init_fields() {
		add_filter( 'ibx_wpfomo_field_fomo_type', array( $this, 'hide_fields' ), 10, 1 );
		add_filter( 'ibx_wpfomo_field_conversions_source', array( $this, 'conversion_source' ), 10, 1 );
		add_filter( 'ibx_wpfomo_field_reviews_source', array( $this, 'reviews_source' ), 10, 1 );
		add_filter( 'ibx_wpfomo_metabox_fields', array( $this, 'add_fields' ), 10, 1 );
	}

	/**
	 * Should be overridden by subclasses to enqueue
	 * additional css/js.
	 *
	 * @since 1.1
	 * @return void
	 */
	public function admin_enqueue_scripts() {
	}

	/**
	 * Register admin settings.
	 *
	 * @since 1.1
	 * @param array $settings
	 * @return array
	 */
	public function register_admin_settings( $settings ) {
		return $settings;
	}

	/**
	 * Conversion fields.
	 *
	 * @since 1.1
	 * @return array
	 */
	abstract public function fields();

	/**
	 * Get conversion fields.
	 *
	 * @since 1.1.2
	 * @return array
	 */
	private function get_fields() {
		$tabs = $this->fields();
		$actual_fields = array();

		foreach ( $tabs as $tab => $sections ) {
			foreach ( $sections as $section => $fields ) {
				if ( isset( $fields['fields'] ) ) {
					$fields = $fields['fields'];
				}
				$actual_fields = array_merge( $actual_fields, $fields );
			}
		}

		return $actual_fields;
	}

	/**
	 * Hide fields when reviews is selected.
	 *
	 * @since 1.1
	 * @param array $data
	 * @return array
	 */
	public function hide_fields( $data ) {
		$tabs = $this->fields();
		$fields = array();

		foreach ( $tabs as $tab => $sections ) {
			foreach ( $sections as $section_key => $section ) {
				if ( isset( $section['fields'] ) ) {
					if ( isset( $section['hide_if'] ) ) {
						$value = explode( '!', $section['hide_if'] );
						if ( '' === $value[0] ) {
							$data['hide'][ $value[1] ]['sections'][] = $section_key;
						} else {
							foreach ( $data['options'] as $option_key => $option_val ) {
								$data['hide'][ $option_key ]['sections'][] = $section_key;
							}
						}
					}
				} else {
					$fields = array_merge( $fields, $section );
				}
			}
		}

		if ( ! empty( $fields ) ) {
			// Hide fields from other field types.
			foreach ( $data['options'] as $option_key => $option_val ) {
				if ( ( $this->have_conversions() && 'conversion' != $option_key ) ||
					( $this->have_reviews() && 'reviews' != $option_key ) ) {
					foreach ( $fields as $key => $field ) {
						if ( isset( $field['hide_if'] ) ) {
							$value = explode( '!', $field['hide_if'] );
							if ( '' === $value[0] ) {
								if ( $option_key != $value[1] ) {
									$data['hide'][ $option_key ]['fields'][] = $key;
								}
							} else {
								$data['hide'][ $value[0] ]['fields'][] = $key;
							}
						} else {
							$data['hide'][ $option_key ]['fields'][] = $key;
						}
					}
				}
			}
		} // End if().

		return $data;
	}

	/**
	 * Fields to be toggled on a converion source change.
	 *
	 * @since 1.1
	 * @return array
	 */
	public function toggle_fields() {
		return array();
	}

	/**
	 * Additional condition to make non-conversion add-ons.
	 *
	 * @since 2.1
	 * @return boolean
	 */
	public function have_conversions() {
		return true;
	}

	/**
	 * Additional condition to make add-ons for reviews.
	 * This needs to be passed true to add reviews fields.
	 *
	 * @since 2.1
	 * @return boolean
	 */
	public function have_reviews() {
		return false;
	}

	/**
	 * Add conversion source.
	 *
	 * @since 1.1
	 * @param array $data
	 * @return array
	 */
	public function conversion_source( $data ) {
		if ( ! $this->have_conversions() ) {
			return $data;
		}

		$data['options'][ $this->slug ] = $this->name;

		$options = $data['options'];
		asort( $options );

		$data['options'] = $options;

		return $this->add_fields_toggles( $data, 'conversion' );
	}

	/**
	 * Add reviews source.
	 *
	 * @since 2.1
	 * @param array $data
	 * @return array
	 */
	public function reviews_source( $data ) {
		if ( ! $this->have_reviews() ) {
			return $data;
		}

		$data['options'][ $this->slug ] = $this->name;

		$options = $data['options'];
		asort( $options );

		$data['options'] = $options;

		return $this->add_fields_toggles( $data, 'reviews' );
	}

	/**
	 * Add fields for conversion.
	 *
	 * @since 1.1
	 * @param array $data
	 * @return array
	 */
	public function add_fields( $data ) {
		$tabs = $this->fields();

		foreach ( $tabs as $tab => $sections ) {
			if ( ! isset( $data[ $tab ] ) ) {
				continue;
			}
			foreach ( $sections as $section => $section_data ) {
				if ( ! isset( $data[ $tab ]['sections'][ $section ] ) ) {
					if ( isset( $section_data['fields'] ) ) {
						$data[ $tab ]['sections'][ $section ] = $section_data;
						// Reposition the section in array if an "insert_after" parameter passed.
						if ( isset( $section_data['insert_after'] ) ) {
							// Get the current position of the section which will be moved.
							$position = array_search( $section_data['insert_after'], array_keys( $data[ $tab ]['sections'] ) );
							// Get the position of current section in loop.
							$position_curr = array_search( $section, array_keys( $data[ $tab ]['sections'] ) );
							// Split and grab the portion of array till current section position.
							$p1 = array_splice( $data[ $tab ]['sections'], $position_curr, 1 );
							// Split and grab the portion of array after the new positioned section.
							$p2 = array_splice( $data[ $tab ]['sections'], 0, ($position + 1) );
							// Add new positioned section between p2 and p1.
							$data[ $tab ]['sections'] = array_merge( $p2, $p1, $data[ $tab ]['sections'] );
						} else {
							$data[ $tab ]['sections'][ $section ] = $section_data;
						}
					} else {
						continue;
					}
				} else {
					if ( ! isset( $section_data['fields'] ) ) {
						$fields = $section_data;
						foreach ( $fields as $key => $field ) {
							$data[ $tab ]['sections'][ $section ]['fields'][ $key ] = $field;
						}
					} else {
						continue;
					}
				}
			}
		} // End foreach().

		return $data;
	}

	/**
	 * Add toggles data for fields.
	 *
	 * @since 2.1
	 * @param array $data
	 * @return array
	 */
	private function add_fields_toggles( $data, $type ) {
		$tabs = $this->fields();
		$fields = array();

		foreach ( $tabs as $tab => $sections ) {
			foreach ( $sections as $section_key => $section ) {
				if ( isset( $section['fields'] ) ) {
					$data['toggle'][ $this->slug ]['sections'][] = $section_key;
				} else {
					// match condition to toggle fields.
					foreach ( $section as $field_key => $field ) {
						if ( isset( $field['hide_if'] ) ) {
							$condition = explode( '!', $field['hide_if'] );
							// if this is != condition.
							// fomo type is not equal to the type mentioned in condition.
							if ( empty( $condition[0] ) ) {
								if ( $type != $condition[1] ) {
									unset( $section[ $field_key ] );
								}
							} else {
								// if this is == condition.
								// fomo type is equal to the type mentioned in condition.
								if ( $type == $condition[0] ) {
									unset( $section[ $field_key ] );
								}
							}
						}
					}
					$fields = array_merge( $fields, array_keys( $section ) );
				}
			}
		}

		$toggle_fields = $this->toggle_fields();

		if ( ! empty( $toggle_fields ) ) {
			$fields = array_merge( $fields, $toggle_fields );
		}

		if ( ! empty( $fields ) ) {
			$data['toggle'][ $this->slug ]['fields'] = $fields;
		}

		return $data;
	}

	/**
	 * Add conversion content.
	 *
	 * @since 1.1
	 * @param array $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( $this->slug != $settings->conversions_source ) {
			return $data;
		}

		return $data;
	}

	/**
	 * Add reviews content.
	 *
	 * @since 2.1
	 * @param array $data
	 * @param object $settings
	 * @return array
	 */
	public function add_reviews_data( $data, $settings ) {
		if ( $this->slug != $settings->reviews_source ) {
			return $data;
		}

		return $data;
	}
}
