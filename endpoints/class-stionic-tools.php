<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Endpoint_Tools extends  WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wp/v2';
		add_action( 'rest_api_init', array( $this, 'register_api_hooks' ) );
	}
	public function register_api_hooks() {
		// final url redirect
		register_rest_route(
			$this->namespace,
			'/m_tools/final_url',
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'final_url' ),
					'args'     => array(
						'url' => array(
							'required'          => true,
							'sanitize_callback' => 'urldecode_deep',
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'/m_tools/deeplinks',
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'deeplinks' ),
					'args'     => array(
						'url' => array(
							'required'          => true,
							'sanitize_callback' => 'urldecode_deep',
						),
					),
				),
			)
		);
	}
	function final_url( $request ) {
		$params = $request->get_params();
		$url    = $params['url'];
		while ( true ) {
			$head = wp_remote_head( $url );
			if ( wp_remote_retrieve_header( $head, 'Location' ) === '' ) {
				break;
			}
			$url = wp_remote_retrieve_header( $head, 'Location' );
		}
		return apply_filters( 'stionic_endpoint_final_url', $url, $request );
	}
	function deeplinks( $request ) {
		$params = $request->get_params();
		$url    = $params['url'];
		$url    = substr( $url, strpos( $url, 'http' ), strlen( $url ) - strpos( $url, 'http' ) );
		$url    = str_replace( array( 'http//', 'https//' ), array( 'http://', 'https://' ), $url );
		$request->set_param( 'url', $url );
		$url = $this->final_url( $request );
		// check post
		$post_id = url_to_postid( $url );
		$data    = false;
		if ( ! empty( $post_id ) ) {
			// get post type
			$type = get_post_type( $post_id );
			// return null if exclude post or page
			if ( ! in_array( $type, array( 'post', 'page' ), true ) ) {
				return null;
			}
			// prepage data
			$data         = array();
			$data['type'] = ( $type === 'page' ? 'page' : 'post' );
			$data['id']   = $post_id;
		} else {
			// check category
			$category_base = get_option( 'category_base' );
			$home_url      = home_url();
			if ( empty( $category_base ) ) {
				$category_base = '/category';
			}
			if ( strpos( $url, $category_base ) === 0 || strpos( $url, $home_url . $category_base ) === 0 ) {
				// url start with /
				if ( strpos( $url, $category_base ) === 0 ) {
					$slug_category = array_filter( explode( '/', $url ) );
					$slug_category = end( $slug_category );
				} else {
					// url start with http
					$category_base = $home_url . $category_base;
					$slug_category = substr( $url, strlen( $category_base ), strlen( $url ) - strlen( $category_base ) );
					$slug_category = array_filter( explode( '/', $slug_category ) );
					$slug_category = end( $slug_category );
				}
				// get category by slug
				$category = get_category_by_slug( $slug_category );
				// if has category
				if ( ! empty( $category ) ) {
					$data         = array();
					$data['type'] = 'category';
					$data['id']   = $category->term_id;
					$data['data'] = array(
						'description' => $category->category_description,
						'id'          => $category->term_id,
						'name'        => $category->name,
					);
				}
			}
		}
		return apply_filters( 'stionic_endpoint_deeplinks', $data, $request );
	}
}
new Stionic_Endpoint_Tools();
