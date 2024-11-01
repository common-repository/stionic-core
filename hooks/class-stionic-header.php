<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Stionic_Hook_Header {
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}
	function rest_api_init() {
		remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
		add_filter( 'rest_pre_serve_request', array( $this, 'rest_pre_serve_request' ) );
	}
	function rest_pre_serve_request( $value ) {
		$origin = get_http_origin();

		if ( $origin ) {
			$settings = get_option( 'stionic_settings' );
			if ( ! empty( $settings['allow_origin'] ) ) {
				$list = preg_split( "/\\r\\n|\\r|\\n/", wp_unslash( $settings['allow_origin'] ) );
				if ( ! in_array( $origin, $list, true ) ) {
					$origin = null;
				}
			}
		}

		if ( $origin ) {
			header( 'Access-Control-Allow-Origin: ' . $origin );
			header( 'Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE' );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Vary: Origin', true );
		} elseif ( ! headers_sent() && isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD'] && ! is_user_logged_in() ) {
			header( 'Vary: Origin', true );
		}

		return $value;
	}
}
new Stionic_Hook_Header();
