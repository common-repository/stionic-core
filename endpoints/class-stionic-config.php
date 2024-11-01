<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Stionic_Endpoint_Config extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wp/v2';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/m_config',
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'get_config' ),
				),
			)
		);
	}
	public function get_config( $request ) {
		global $wpdb;
		// get option from database
		$data      = get_option( 'stionic_settings' );
		$onesignal = get_option( 'stionic_onesignal' );
		if ( isset( $onesignal['onesignal_app_id'] ) ) {
			$data['onesignal_app_id'] = $onesignal['onesignal_app_id'];
		}
		$categories = get_option( 'stionic_categories' );
		if ( isset( $categories['show_all'] ) ) {
			$data['show_all_categories'] = $categories['show_all'];
		}
		// extend data
		$data['require_name_email'] = (bool) get_option( 'require_name_email' );
		$data['last_time']          = strtotime( $wpdb->get_var( "SELECT max(post_modified_gmt) FROM $wpdb->posts" ) );
		$data['extended']           = Stionic_Functions::decode_json_option( $data['extended'] );
		return apply_filters( 'stionic_endpoint_config', $data, $request );
	}
}
new Stionic_Endpoint_Config();
