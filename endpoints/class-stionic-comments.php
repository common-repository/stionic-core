<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Endpoint_Comments extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'wp/v2';
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		// allow anonymous comment
		add_filter( 'rest_allow_anonymous_comments', '__return_true' );
	}
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/m_comments/(?P<post>[\\d]+)',
			array(
				array(
					'methods'  => 'GET',
					'callback' => array( $this, 'get_comments' ),
					'args'     => array(
						'post'     => array(
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
						'parent'   => array(
							'required'          => true,
							'sanitize_callback' => 'absint',
						),
						'page'     => array(
							'sanitize_callback' => 'absint',
							'default'           => 1,
						),
						'per_page' => array(
							'sanitize_callback' => 'absint',
							'default'           => 10,
							'validate_callback' => function( $param ) {
								return ( $param > 0 && $param <= 20 );
							},
						),
					),
				),
			)
		);
	}
	public function get_comments( $request ) {
		global $wpdb;
		// get data
		$params   = $request->get_params();
		$filter   = array(
			'orderby' => 'comment_date_gmt',
			'parent'  => $params['parent'],
			'post_id' => $params['post'],
			'status'  => 'approve',
			'offset'  => ( $params['page'] * $params['per_page'] ) - $params['per_page'],
			'number'  => $params['per_page'],
		);
		$comments = get_comments( apply_filters( 'stionic_endpoint_comments_args', $filter, $request ) );
		// rewrite response
		$data = array();
		foreach ( $comments as $comment ) {
			$query  = $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_parent=%d", $comment->comment_ID );
			$avatar = get_avatar( $comment->user_id, 64 );
			if ( ! empty( $avatar ) ) {
				preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $avatar, PREG_SET_ORDER );
			}
			$now = array(
				'avatar'    => ! empty( $avatar ) ? wp_specialchars_decode( $avatar[0][1] ) : null,
				'content'   => $comment->comment_content,
				'date'      => gmdate( 'c', strtotime( $comment->comment_date_gmt ) ),
				'has_child' => (bool) $wpdb->get_var( $query ),
				'id'        => intval( $comment->comment_ID ),
				'name'      => $comment->user_id ? get_the_author_meta( 'display_name', $comment->user_id ) : $comment->comment_author,
				'user_id'   => intval( $comment->user_id ),
			);
			array_push( $data, $now );
		}
		return apply_filters( 'stionic_endpoint_comments', $data, $request );
	}
}
new Stionic_Endpoint_Comments();
