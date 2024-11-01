<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Endpoint_Pages extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wp/v2';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/m_pages',
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'get_pages' ),
				),
			)
		);
	}
	public function get_pages( $request ) {
		global $wpdb;
		// get data
		$filter = array(
			'sort_column'  => 'menu_order',
			'hierarchical' => 0,
			'meta_key'     => '_show_in_application',
			'meta_value'   => '1',
			'post_type'    => 'page',
			'post_status'  => 'publish',
		);
		$pages  = get_pages( apply_filters( 'stionic_endpoint_pages_args', $filter, $request ) );
		// rewrite response
		$data = array();
		foreach ( $pages as $page ) {
			$now = array(
				'id'    => $page->ID,
				'image' => get_the_post_thumbnail_url( $page->ID ),
				'title' => $page->post_title,
			);
			array_push( $data, $now );
		}
		return apply_filters( 'stionic_endpoint_pages', $data, $request );
	}
}
new Stionic_Endpoint_Pages();
