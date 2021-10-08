<?php

class IBX_WPFomo_API {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	public static $instance;

	/**
	 * Holds the namespace for API.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $namespace;

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = 'wpfomify/v2';

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/trigger/', array(
			'methods'   => 'GET',
			'callback'  => array( $this, 'response' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( $this->namespace, '/notification/(?P<id>\d+)', array(
			'methods'   => 'POST',
			'callback'  => array( $this, 'response' ),
			'args'      => array(
				'id' => array(
					'validate_callback' => function( $param, $request, $key ) {
						return is_numeric( $param );
					},
				),
			),
			'permission_callback' => '__return_true',
		) );

		do_action( 'ibx_wpfomo_api_register_routes', $this->namespace );
	}

	/**
	 * Callback function for REST API.
	 *
	 * @since 1.0.0
	 */
	public function response( WP_REST_Request $request ) {
		$response = array(
			'data'      => '',
			'error'     => false,
		);

		if ( ! isset( $request['api_key'] ) ) {
			$response['error'] = __( 'Error: You must provide an API key.', 'ibx-wpfomo' );
		} elseif ( IBX_WPFomo_Helper::api_key() != $request['api_key'] ) {
			$response['error'] = __( 'Error: Invalid API key.', 'ibx-wpfomo' );
		}

		if ( ! $response['error'] ) {
			$response['data'] = $request->get_params();
			if ( isset( $response['data']['api_key'] ) ) {
				unset( $response['data']['api_key'] );
			}
			// Hook custom action.
			do_action( 'ibx_wpfomo_api_response_success', $response['data'] );
		}

		return apply_filters( 'ibx_wpfomo_api_response', $response );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @return object The IBX_WPFomo_API object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof IBX_WPFomo_API ) ) {
			self::$instance = new IBX_WPFomo_API();
		}

		return self::$instance;
	}
}

// Instantiate the class.
$ibx_wpfomo_api = IBX_WPFomo_API::get_instance();
