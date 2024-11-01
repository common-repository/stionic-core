<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Includes_Posts extends WP_REST_Controller {
	public function __construct() {
		// when rest api init
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}
	public function register_routes() {
		// add featured_url field to post, page endpoint
		register_rest_field(
			array( 'post', 'page' ),
			'featured_url',
			array(
				'get_callback'    => array( $this, 'featured_url' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// add featured_square_url field to post, page endpoint
		register_rest_field(
			array( 'post', 'page' ),
			'featured_square_url',
			array(
				'get_callback'    => array( $this, 'featured_square_url' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// add m_comment_count field to post, page endpoint
		register_rest_field(
			array( 'post', 'page' ),
			'm_comment_count',
			array(
				'get_callback'    => array( $this, 'm_comment_count' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// add m_categories field to post endpoint
		register_rest_field(
			'post',
			'm_categories',
			array(
				'get_callback'    => array( $this, 'm_categories' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// add m_next_post field to post endpoint
		register_rest_field(
			'post',
			'm_next_post',
			array(
				'get_callback'    => array( $this, 'm_next_post' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// add m_previous_post field to post endpoint
		register_rest_field(
			'post',
			'm_previous_post',
			array(
				'get_callback'    => array( $this, 'm_previous_post' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
		// add required_rewards_ads field to post endpoint
		register_rest_field(
			array( 'post', 'page' ),
			'required_rewards_ads',
			array(
				'get_callback'    => array( $this, 'required_rewards_ads' ),
				'update_callback' => null,
				'schema'          => null,
			)
		);
	}
	function featured_url( $object ) {
		// get default settings featured images
		$option_media = get_option( 'stionic_media' );
		if ( ! empty( $option_media ) && isset( $option_media['disable_featured'] ) ) {
			$disable_featured = $option_media['disable_featured'];
		}
		$exclude_featured = get_post_meta( $object['id'], '_stionic_exclude_featured', true );
		// return featured_url if this post has chose show featured image
		if ( ( empty( $disable_featured ) && ! empty( $exclude_featured ) ) || ( ! empty( $disable_featured ) && empty( $exclude_featured ) ) ) {
			return null;
		} else {
			return get_the_post_thumbnail_url( $object['id'], 'full' );
		}
	}
	function featured_square_url( $object ) {
		// return featured_square_url
		return get_the_post_thumbnail_url( $object['id'], 'stionic_square' );
	}
	function m_comment_count( $object ) {
		// get comment_count
		return intval( get_comments_number( $object['id'] ) );
	}
	function m_categories( $object ) {
		// get list category
		$categories = get_the_category( $object['id'] );
		$data       = array();
		// prepare data return
		foreach ( $categories as $category ) {
			$now = array(
				'description' => $category->category_description,
				'id'          => $category->term_id,
				'name'        => $category->name,
			);
			array_push( $data, $now );
		}
		return $data;
	}
	function m_next_post( $object ) {
		$next_post = get_next_post();
		if ( empty( $next_post ) ) {
			return null;
		}
		return $next_post->ID;
	}
	function m_previous_post( $object ) {
		$previous_post = get_previous_post();
		if ( empty( $previous_post ) ) {
			return null;
		}
		return $previous_post->ID;
	}
	function required_rewards_ads( $object ) {
		// return featured_square_url
		$rewardAfter = get_post_meta( $object['id'], '_stionic_required_rewards_ads', true );
		if ( $rewardAfter === '' ) {
			$setting = get_option( 'stionic_settings' );
			if ( isset( $setting['ads_default_reward'] ) ) {
				$rewardAfter = $setting['ads_default_reward'];
			}
		}
		return intval( $rewardAfter );
	}
}
new Stionic_Includes_Posts();
