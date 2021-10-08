<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_GForms extends IBX_WPFomo_Addon {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Primary class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'name'  => 'Gravity Forms',
				'slug'  => 'gravity-forms',
				'dir'   => IBX_WPFOMO_GFORMS_DIR,
				'url'   => IBX_WPFOMO_GFORMS_URL,
			)
		);

		require_once $this->dir . 'classes/class-ibx-wpfomo-gforms-helper.php';

		add_action( 'ibx_wpfomo_save_post', array( $this, 'clear_transients' ) );
		add_action( 'wp_ajax_wpfomo_gforms_get_fields', array( $this, 'get_form_fields' ) );
	}

	/**
	 * Clear transient on post save.
	 *
	 * @since 1.0.0
	 */
	public function clear_transients( $post_id ) {
		if ( isset( $_POST['ibx_wpfomo_conversions_source'] ) && $this->slug == $_POST['ibx_wpfomo_conversions_source'] ) {
			delete_transient( 'ibx_wpfomo_gforms_data_' . $post_id );
		}
	}

	/**
	 * Enqueue scripts in admin.
	 *
	 * @since 1.0.0
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-gforms', $this->url . 'assets/js/meta.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
	}

	/**
	 * Conversion fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function fields() {
		$fields = array(
			'content'	=> array(
				'content_section'	=> array(
					'gf_form'    => array(
						'type'      => 'select',
						'label'     => __( 'Select form', 'ibx-wpfomo' ),
						'default'   => '',
						'options'   => IBX_WPFomo_GForms_Helper::get_forms_list(),
					),
					'gf_field_email'	=> array(
						'type'		=> 'select',
						'label'		=> __( 'Email field', 'ibx-wpfomo' ),
						'default'	=> '',
						'options'	=> array(),
					),
					'gf_template'  => array(
						'type'          => 'template',
						'label'         => __( 'Notification Template', 'ibx-wpfomo' ),
						'rows'          => 3,
						'default'       => array(
							'0'				=> __( 'Someone submitted', 'ibx-wpfomo' ),
							'1'				=> '{{title}}',
							'2'				=> '{{time}}',
						),
						'variables'		=> array( '{{title}}', '{{time}}' ),
						'sanitize'		=> false,
					),
					'gf_custom_url'    => array(
						'type'      => 'text',
						'label'     => __( 'Link to', 'ibx-wpfomo' ),
						'default'   => '',
					),
				),
			),
		);

		return $fields;
	}

	/**
	 * Add conversions.
	 *
	 * @since 1.0.0
	 * @param array $data
	 * @param object $settings
	 * @return array
	 */
	public function add_conversion_data( $data, $settings ) {
		if ( $this->slug != $settings->conversions_source ) {
			return $data;
		}

		if ( empty( $settings->gf_form ) ) {
			return $data;
		}

		// transient key.
		$transient_key = 'ibx_wpfomo_gforms_data_' . $settings->post_id;

		// get data from transient and unserialize it.
		$cache_data = maybe_unserialize( get_transient( $transient_key ) );

		// return data if exist in transient.
		if ( is_array( $cache_data ) && ! empty( $cache_data ) ) {
			return $cache_data;
		}

		// get time settings for entries to be fetched.
		$entries_days 	= empty( $settings->display_last_days ) ? 3 : $settings->display_last_days;
		// convert time into seconds.
		$entries_time 	= $entries_days * 24 * 60 * 60;
		// get current time.
		$current_time 	= time();
		// get timezone offset.
		$gmt_offset		= get_option( 'gmt_offset' );
		// set start time.
		$start_time 	= date( 'Y-m-d', ($current_time - $entries_time) + ($gmt_offset * HOUR_IN_SECONDS * 2) );
		// set end time.
		$end_time 		= date( 'Y-m-d H:i:s', $current_time + ($gmt_offset * HOUR_IN_SECONDS * 2) );

		// search criteria for entries.
		$search_criteria = array(
			'start_date'	=> $start_time . ' 00:00:00',
			'end_date'		=> $end_time,
			'status'		=> 'active',
		);

		// get entries.
		$entries = IBX_WPFomo_GForms_Helper::get_form_entries( $settings->gf_form, $settings->display_last, $search_criteria );

		if ( ! is_array( $entries ) || empty( $entries ) ) {
			return $data;
		}

		// get form object.
		$gf_form = GFAPI::get_form( $settings->gf_form );

		// get form fields.
		$gf_fields = IBX_WPFomo_GForms_Helper::get_form_fields( $settings->gf_form );

		$gf_fields_type = array();

		foreach ( $gf_form['fields'] as $field ) {
			$field_id = $field->id;

			$gf_fields_type[ 'gf_field_' . $field_id ] = array(
				'type'		=> $field->type,
				'inputs'	=> array(),
			);

			if ( 'radio' == $field->type ) {
				$gf_fields_type[ 'gf_field_' . $field_id ]['choices'] = $field->choices;
			}

			if ( 'checkbox' == $field->type ) {

				foreach ( $field->inputs as $choice ) {
					$gf_fields_type[ 'gf_field_' . $choice['id'] ] = array(
						'type'		=> $field->type,
						'inputs'	=> array(),
					);
					$gf_fields_type[ 'gf_field_' . $choice['id'] ]['choices'] = $field->choices;
				}
			}
		}

		// get email field value from settings.
		$gf_field_email = $settings->gf_field_email;

		$name = __( 'Someone', 'ibx-wpfomo' );

		foreach ( $entries as $entry ) {

			$email = '';

			if ( ! empty( $gf_field_email ) && isset( $entry[ $gf_field_email ] ) ) {
				$email = $entry[ $gf_field_email ];
			}

			if ( isset( $entry['partial_entry_percent'] ) && absint( $entry['partial_entry_percent'] ) < 100 ) {
				continue;
			}

			// Data to render notification.
			$fields_data = array(
				'title'     => $gf_form['title'],
				'name'      => '',
				'email'     => $email,
				'time'      => '',
			);

			// Custom variables.
			foreach ( $gf_fields as $id => $label ) {
				$field_var 		= 'gf_field_' . $id;
				$field_label 	= isset( $entry[ $id ] ) ? $entry[ $id ] : '';
				$name_field_var = '';

				if ( empty( $field_label ) && isset( $gf_fields_type[ $field_var ] ) && 'name' == $gf_fields_type[ $field_var ]['type'] ) {
					$first_id 	= $id . '.3';
					$last_id 	= $id . '.6';

					$field_label 	= isset( $entry[ $first_id ] ) ? $entry[ $first_id ] : '';

					if ( ! empty( $field_label ) ) {
						$field_label 	.= isset( $entry[ $last_id ] ) ? ' ' . $entry[ $last_id ][0] . '.' : '';
					}

					if ( empty( $field_label ) ) {
						$field_label = $name;
					}

					$name_field_var = $field_var;

					$fields_data['name'] = $field_label;
				} elseif ( ! empty( $field_label ) && isset( $gf_fields_type[ $field_var ] ) && 'radio' == $gf_fields_type[ $field_var ]['type'] ) {
					foreach ( $gf_fields_type[ $field_var ]['choices'] as $choice ) {
						if ( $choice['value'] == $field_label ) {
							$field_label = $choice['text']; //replace option value by label.
						}
					}
				} elseif ( ! empty( $field_label ) && isset( $gf_fields_type[ $field_var ] ) && ('checkbox' == $gf_fields_type[ $field_var ]['type'] ) ) {
					foreach ( $gf_fields_type[ $field_var ]['choices'] as $choice ) {
						if ( $choice['value'] == $field_label ) {
							$field_label = $choice['text']; //replace option value by label.
						}
					}
				}

				$fields_data[ 'gf_field_' . $id ] = $field_label;

				if ( empty( $fields_data['name'] ) && ! empty( $name_field_var ) ) {
					$fields_data['name'] = isset( $fields_data[ $name_field_var ] ) ? $fields_data[ $name_field_var ] : '';
				}
			} // End foreach().

			// Time
			if ( isset( $entry['date_created'] ) && ! empty( $entry['date_created'] ) ) {
				$local_date = date( 'Y-m-d H:i:s', strtotime( $entry['date_created'] ) + (get_option( 'gmt_offset' ) * 3600) );
				$fields_data['time'] = IBX_WPFomo_Helper::get_timeago_html( $local_date );
			}

			// Link
			$fields_data['url'] = esc_url( $settings->gf_custom_url );

			$data['fields'][] = $fields_data;
		} // End foreach().

		$data['template'] = $settings->gf_template;

		// store data in transient for the given time in options.
		$cache_duration = IBX_WPFomo_Admin::get_settings( 'cache_duration' );
		if ( ! $cache_duration || empty( $cache_duration ) ) {
			$cache_duration = 45;
		}

		set_transient( $transient_key, maybe_serialize( $data ), ( $cache_duration / 60 ) * HOUR_IN_SECONDS );

		return $data;
	}

	/**
	 * Get form fields on AJAX request.
	 *
	 * @hook wp_ajax_wpfomo_gforms_get_fields
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function get_form_fields() {
		$response = array(
			'error' => false,
			'data'	=> array(
				'options'	=> '',
				'tags'		=> array(),
			),
		);

		if ( ! isset( $_POST['form_id'] ) || empty( $_POST['form_id'] ) ) {
			$response['error'] = __( 'Form ID is not provided.', 'ibx-wpfomo' );
		}

		if ( ! $response['error'] ) {

			$fields = IBX_WPFomo_GForms_Helper::get_form_fields( absint( wp_unslash( $_POST['form_id'] ) ) );

			$response['data']['options'] = '<option value="">' . __( '-- Select --', 'ibx-wpfomo' ) . '</option>';

			foreach ( $fields as $id => $label ) {
				$alt_label = '' === $label ? 'gf_field_' . $id : $label;
				$response['data']['options'] .= '<option value="' . $id . '">' . $alt_label . '</option>';
				$response['data']['tags'][ '{{gf_field_' . $id . '}}' ] = $label;
			}
		}

		echo json_encode( $response );
		die();
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_GForms object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_GForms ) ) {
			self::$instance = new IBX_WPFomo_GForms();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_gforms = IBX_WPFomo_GForms::get_instance();
