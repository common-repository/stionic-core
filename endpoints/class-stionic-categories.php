<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Endpoint_Categories extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wp/v2';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/m_categories',
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'get_categories' ),
					'args'     => array(
						'parent'    => array(
							'sanitize_callback' => 'absint',
						),
						'include'   => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'exclude'   => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'over_hide' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'has_posts' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
			)
		);
	}
	public function get_categories( $request ) {
		// require plugin function
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'categories-images/categories-images.php' ) ) {
			require_once WP_PLUGIN_DIR . '/categories-images/categories-images.php';
			$has_categories_image = true;
		}
		// get data
		$params            = $request->get_params();
		$hide              = empty( $params['over_hide'] ) ? get_option( 'stionic-categories-hide', array() ) : array();
		$filter            = array(
			'orderby'    => 'term_group',
			'hide_empty' => 0,
			'include'    => array(),
			'exclude'    => array(),
		);
		$option_categories = get_option( 'stionic_categories' );
		if ( ! empty( $option_categories ) ) {
			if ( empty( $option_categories['check_show'] ) ) {
				$filter['exclude'] = $hide;
			} elseif ( empty( $params['include'] ) ) {
				$filter['include'] = $hide;
			}
		}
		if ( isset( $params['parent'] ) ) {
			$filter['parent'] = $params['parent'];
		}
		if ( ! empty( $params['include'] ) ) {
			$params['include'] = array_filter( array_map( 'intval', explode( ',', $params['include'] ) ) );
			$filter['include'] = array_merge( $filter['include'], $params['include'] );
		}
		if ( ! empty( $params['exclude'] ) ) {
			$params['exclude'] = array_filter( array_map( 'intval', explode( ',', $params['exclude'] ) ) );
			$filter['exclude'] = array_merge( $filter['exclude'], $params['exclude'] );
		}
		$categories = get_categories( apply_filters( 'stionic_endpoint_categories_args', $filter, $request ) );
		// rewrite response
		$data = array();
		if ( ! empty( $params['has_posts'] ) ) {
			$postsRequest = new WP_Rest_Request( 'GET', '/wp/v2/m_posts' );
		}
		foreach ( $categories as $category ) {
			$now = array(
				'description' => $category->description,
				'id'          => $category->term_id,
				'parent'      => $category->parent,
				'name'        => $category->name,
			);
			if ( ! empty( $has_categories_image ) ) {
				$now['image'] = z_taxonomy_image_url( $category->term_id );
			} else {
				$now['image'] = null;
			}
			if ( ! empty( $postsRequest ) ) {
				$postsRequest->set_query_params(
					apply_filters(
						'stionic_endpoint_categories_posts_args',
						array(
							'categories' => $now['id'],
							'per_page'   => 5,
						),
						$request
					)
				);
				$postsResponse = rest_do_request( $postsRequest );
				if ( empty( $postsResponse->data ) ) {
					continue;
				}
				$now['posts'] = $postsResponse->data;
			}
			array_push( $data, $now );
		}
		return apply_filters( 'stionic_endpoint_categories', $data, $request );
	}
}
new Stionic_Endpoint_Categories();
