<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for helper functions.
 *
 * @since 2.1
 */
class IBX_WPFomo_Google_Reviews_Helper {
	public static function get_default_place_id() {
		return 'ChIJjZwurGTlZzkRLVOKFH8rKYc';
	}

	public static function check_api( $api_key ) {
		$error = false;

		$place_id = self::get_default_place_id();

		$response = self::get_api_response( $api_key, $place_id );

		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} else {
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$data = json_decode( wp_remote_retrieve_body( $response ) );
				if ( 'OK' !== $data->status ) {
					$error = $data->error_message;
				}
			}
		}

		if ( ! $error ) {
			return 'success';
		} else {
			return $error;
		}
	}

	public static function get_api_response( $api_key, $place_id ) {
		$url = add_query_arg(
			array(
				'key'     => $api_key,
				'placeid' => $place_id,
				'language' => get_locale(),
			),
			'https://maps.googleapis.com/maps/api/place/details/json'
		);

		$response = wp_remote_post(
			$url,
			array(
				'method'      => 'POST',
				'timeout'     => 60,
				'httpversion' => '1.0',
				'sslverify'   => false,
			)
		);

		return $response;
	}

	public static function get_place_data( $api_key, $place_id ) {
		$response = self::get_api_response( $api_key, $place_id );
		$output = array(
			'data'	=> array(),
			'error' => false,
		);

		if ( is_wp_error( $response ) ) {
			$output['error'] = $response->get_error_message();
		} else {
			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$data = json_decode( wp_remote_retrieve_body( $response ) );
				if ( 'OK' !== $data->status ) {
					$output['error'] = $data->error_message;
				} else {
					if ( isset( $data->result ) ) {
						$result = $data->result;
						$output['data']['name'] = isset( $result->name ) ? $result->name : '';
						$output['data']['icon'] = isset( $result->icon ) ? $result->icon : '';
						$output['data']['map'] = isset( $result->url ) ? $result->url : '';
						$output['data']['website'] = isset( $result->website ) ? $result->website : '';
						$output['data']['reviews'] = isset( $data->result ) && isset( $data->result->reviews ) ? $data->result->reviews : array();
					}
					$output['error'] = false;
				}
			}
		}

		return $output;
	}

	public function parse_reviews( $reviews, $orderby = 'time', $order = 'ASC' ) {
		$data = array();

		foreach ( $reviews as $review ) {
			$data[ $review->time ] = array(
				'author_name'	=> $review->author_name,
				'author_url'	=> $review->author_url,
				'author_image'	=> $review->profile_photo_url,
				'rating'		=> $review->rating,
				'relative_time'	=> $review->relative_time_description,
				'time'			=> $review->time,
				'text'			=> $review->text,
			);
		}

		ksort( $data );

		return $data;
	}
}
