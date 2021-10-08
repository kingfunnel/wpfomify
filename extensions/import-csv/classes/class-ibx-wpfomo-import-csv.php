<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class IBX_WPFomo_Import_CSV extends IBX_WPFomo_Addon {
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
		parent::__construct( array(
			'name'  => 'CSV File',
			'slug'  => 'import_csv',
			'dir'   => IBX_WPFOMO_IMPORT_CSV_DIR,
			'url'   => IBX_WPFOMO_IMPORT_CSV_URL,
		) );
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_script( 'ibx-wpfomo-import-csv', $this->url . 'assets/js/meta.js', array( 'jquery' ), IBX_WPFOMO_VER, true );
	}

	/**
	 * Conversion fields.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function fields() {
		$fields = array(
			'configuration'	=> array(
				'import_csv'           => array(
					'title'       => __( 'Import CSV', 'ibx-wpfomo' ),
					'description' => __( 'Import conversion data from CSV file.', 'ibx-wpfomo' ),
					'insert_after'	=> 'config',
					'hide_if'	=> '!conversion',
					'fields'      => array(
						'csv_import_file'     => array(
							'type'        => 'file',
							'label'       => __( 'Select file', 'ibx-wpfomo' ),
							'description' => __( 'Select CSV file and click "Upload"', 'ibx-wpfomo' ),
							'accept'      => '.csv',
							'action'	  => 'ibx_wpfomo_upload_file',
							'sanitize'    => false,
							'priority'    => 500,
						),
						'download_sample_csv' => array(
							'type'     => 'button',
							'label'    => __( 'Download Sample CSV file', 'ibx-wpfomo' ),
							'text'     => __( 'Download Sample CSV', 'ibx-wpfomo' ),
							'url'      => IBX_WPFOMO_URL . 'data/import.csv',
							'download' => 'sample.csv',
							'priority' => 500,
						),
					),

				),
				'csv_fields_section'   => array(
					'title'  => __( 'Fields Mapping', 'ibx-wpfomo' ),
					'insert_after'	=> 'import_csv',
					'class'	=> 'mbt-is-hidden',
					'hide_if'	=> '!conversion',
					'fields' => array(
						'csv_title_field'   => array(
							'type'    => 'select',
							'class'   => 'csv_column_fields',
							'label'   => __( 'Product Title', 'ibx-wpfomo' ),
							'default' => '',
							'options' => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),

						),
						'csv_email_field'   => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'Email', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
						'csv_name_field'    => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'Name', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
						'csv_time_field'    => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'Time', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
						'csv_country_field' => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'Country', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
						'csv_state_field'   => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'State', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
						'csv_city_field'    => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'City', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
						'csv_image_field'   => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'Image Url', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
						'csv_url_field'     => array(
							'type'     => 'select',
							'class'    => 'csv_column_fields',
							'label'    => __( 'Product Url', 'ibx-wpfomo' ),
							'default'  => '',
							'options'  => array(
								'' => __( 'Select', 'ibx-wpfomo' ),
							),
							'priority' => 500,
						),
					),
				),
			),
			'content'	=> array(
				'content_section'	=> array(
					'csv_form_entries'       => array(
						'type'  => 'button',
						'label' => __( 'Entries', 'ibx-wpfomo' ),
						'text'  => __( 'Show Entries', 'ibx-wpfomo' ),
						'url'   => IBX_WPFomo_List_Helper::get_button_url( 'csv' ),
					),
				),
			),
		);

		return $fields;
	}

	public function toggle_fields() {
		return array(
			'notification_custom_form_msg',
			'csv_form_entries',
		);
	}

	public function add_conversion_data( $data, $settings ) {
		if ( $this->slug != $settings->conversions_source ) {
			return $data;
		}

		$id = isset( $id ) ? $id : $settings->post_id;

		$display_last      = $settings->display_last;
		$display_last_days = $settings->display_last_days;

		$where_condition = '';
		$limit_condition = '';

		if ( ! empty( $display_last ) ) {
			$limit_condition = " LIMIT $display_last";
		}

		if ( ! empty( $display_last_days ) ) {
			$where_condition = " AND src IN ('csv') AND time >= ( CURDATE() - INTERVAL $display_last_days DAY )";
		}

		$order_by			= '';
		$conversion_data	= IBX_WPFomo_Conversion::get_conversion_data( $id, $where_condition, $order_by, $limit_condition );
		$data['fields']   	= $conversion_data;
		$data['template'] 	= $settings->notification_custom_form_msg;

		return $data;
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_Import_CSV object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_Import_CSV ) ) {
			self::$instance = new IBX_WPFomo_Import_CSV();
		}

		return self::$instance;
	}
}

$ibx_wpfomo_import_csv = IBX_WPFomo_Import_CSV::get_instance();
