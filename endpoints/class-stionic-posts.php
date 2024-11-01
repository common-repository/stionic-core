<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Endpoint_Posts extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wp/v2';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/m_posts',
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'get_posts' ),
					'args'     => array(
						'fields'             => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'categories'         => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'exclude_categories' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'tags'               => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'page'               => array(
							'sanitize_callback' => 'absint',
							'default'           => 1,
						),
						'per_page'           => array(
							'sanitize_callback' => 'absint',
							'default'           => 10,
							'validate_callback' => function( $param ) {
								return ( $param > 0 && $param <= 20 );
							},
						),
						'search'             => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'orderby'            => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'include'            => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'exclude'            => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'sticky'             => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'format'             => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'type'               => array(
							'sanitize_callback' => 'sanitize_text_field',
							'default'           => 'post',
						),
					),
				),
			)
		);
	}
	public function get_posts( $request ) {
		$params = $request->get_params();
		if ( ! empty( $params['fields'] ) ) {
			$params['fields'] = explode( ',', $params['fields'] );
		} else {
			$params['fields'] = array();
		}

		$filter = array(
			'ignore_sticky_posts' => 1,
			'post_type'           => $params['type'],
			'paged'               => $params['page'],
			'posts_per_page'      => $params['per_page'],
			'meta_query'          => array(),
		);

		if ( current_user_can( 'read_private_posts' ) ) {
			$filter['post_status'] = array( 'publish', 'private' );
		}

		if ( ! empty( $params['search'] ) ) {
			$filter['s'] = $params['search'];
		}
		if ( ! empty( $params['include'] ) ) {
			$filter['post__in'] = explode( ',', $params['include'] );
		}
		if ( ! empty( $params['exclude'] ) ) {
			$filter['post__not_in'] = explode( ',', $params['exclude'] );
		}
		if ( ! empty( $params['categories'] ) ) {
			$filter['category__in'] = explode( ',', $params['categories'] );
		}
		if ( ! empty( $params['exclude_categories'] ) ) {
			$filter['category__not_in'] = explode( ',', $params['exclude_categories'] );
		}
		if ( ! empty( $params['tags'] ) ) {
			$filter['tag__in'] = explode( ',', $params['tags'] );
		}
		if ( ! empty( $params['orderby'] ) ) {
			$filter['orderby'] = $params['orderby'];
		}
		if ( ! empty( $params['sticky'] ) ) {
			$sticky = get_option( 'sticky_posts' );
			if ( empty( $sticky ) ) {
				return array();
			}
			$filter['post__in'] = $sticky;
			if ( ! isset( $params['per_page'] ) ) {
				$filter['posts_per_page'] = -1;
			}
		}
		if ( ! empty( $params['format'] ) ) {
			array_push(
				$filter['meta_query'],
				array(
					'key'   => '_stionic_post_format',
					'value' => $params['format'],
				)
			);
		}

		// get list
		$posts = new WP_Query( apply_filters( 'stionic_endpoint_posts_args', $filter, $request ) );

		// rewrite response
		$data = array();
		if ( is_array( $posts->posts ) ) {
			foreach ( $posts->posts as $post ) {
				$GLOBALS['post'] = $post;
				setup_postdata( $post );
				$excerpt = apply_filters( 'the_excerpt', apply_filters( 'get_the_excerpt', $post->post_excerpt, $post ) );

				$now = array(
					'comment'      => intval( $post->comment_count ),
					'excerpt'      => $excerpt,
					'categories'   => wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) ),
					'id'           => intval( $post->ID ),
					'link'         => get_permalink( $post->ID ),
					'image'        => get_the_post_thumbnail_url( $post->ID, 'stionic_thumbnail' ),
					'image_square' => get_the_post_thumbnail_url( $post->ID, 'stionic_square' ),
					'date'         => gmdate( 'c', strtotime( $post->post_date_gmt ) ),
					'format'       => get_post_meta( $post->ID, '_stionic_post_format', true ) ? : 'standard',
					'title'        => $post->post_title,
				);

				if ( in_array( 'content', $params['fields'], true ) ) {
					$content        = apply_filters( 'the_content', apply_filters( 'get_the_content', $post->post_content, $post ) );
					$now['content'] = $content;
				}

				array_push( $data, $now );
			}
		}
		return apply_filters( 'stionic_endpoint_posts', $data, $request );
	}
}
new Stionic_Endpoint_Posts();
