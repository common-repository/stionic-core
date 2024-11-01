<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Admin_Metaboxes {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'stionic_metabox' ) );
		add_action( 'save_post', array( $this, 'stionic_save_post' ), 10, 2 );
		add_action( 'publish_future_post', array( $this, 'stionic_future_post' ) );
		add_filter( 'stionic_notification_filter', array( $this, 'stionic_notification_filter' ), 10, 2 );
	}
	function stionic_metabox() {
		global $post;
		// if not post or page
		if ( $post->post_type != 'post' && $post->post_type != 'page' ) {
			return;
		}
		// add metabox
		add_meta_box(
			'stionic-application',
			__( 'Stionic application', 'stionic-core' ),
			array( $this, 'create_stionic_metabox' ),
			$post->post_type,
			'side',
			'high'
		);
	}
	function create_stionic_metabox() {
		global $post;
		// get value in database
		$value = get_post_meta( $post->ID );
		if ( ! empty( $value['_stionic_future_notification'] ) ) {
			$value['_stionic_future_notification'][0] = maybe_unserialize( $value['_stionic_future_notification'][0] );
		} elseif ( $post->post_status != 'future' ) {
			$option_onesignal = get_option( 'stionic_onesignal' );
		}

		// Use nonce for verification
		echo '<input type="hidden" name="stionic_metabox_nonce" value="' . esc_attr( wp_create_nonce( basename( __FILE__ ) ) ) . '" />';

		// Select post format
		if ( $post->post_type == 'post' ) {
			echo '<div id="stionic-format-post">
                <label class="dashicons dashicons-visibility" style="margin:3px 4px 0 0"></label><span>' . esc_html__( 'Post format', 'stionic-core' ) . '</span>
                <select name="_stionic_post_format">
                    <option value="standard" ' . ( empty( $value['_stionic_post_format'] ) ? 'selected' : null ) . '>' . esc_html__( 'Standard', 'stionic-core' ) . '</option>
                    <option value="play" ' . ( @$value['_stionic_post_format'][0] == 'play' ? 'selected' : null ) . '>' . esc_html__( 'Play', 'stionic-core' ) . '</option>
                    <option value="image" ' . ( @$value['_stionic_post_format'][0] == 'image' ? 'selected' : null ) . '>' . esc_html__( 'Image', 'stionic-core' ) . '</option>
                </select>
            </div>';
		}

		// Checkbox show featured top detail page
		echo '<p><label for="stionic-exclude-featured">
            <input id="stionic-exclude-featured" type="checkbox" name="_stionic_exclude_featured" ' . ( ! empty( $value['_stionic_exclude_featured'] ) ? 'checked' : null ) . ' />';
		esc_html_e( ' Exclude default setting featured image', 'stionic-core' ) . '</label></p>';

		// Checkbox show in application
		if ( $post->post_type == 'page' ) {
			echo '<p><input type="checkbox" name="_show_in_application" ' . ( ! empty( $value['_show_in_application'] ) ? 'checked' : null ) . ' />';
			esc_html_e( ' Show in application', 'stionic-core' ) . '</p>';
		}

		echo '<div><b>' . esc_html__( 'Notifications', 'stionic-core' ) . '</b></div>';
		// Checkbox send notification
		echo '<p><label for="stionic-notification">
            <input id="stionic-notification" type="checkbox" name="_notification" ' . ( ! empty( $value['_stionic_future_notification'] ) ? 'checked' : ( ! empty( $option_onesignal['default_send'] ) ? 'checked' : '' ) ) . ' />';
		esc_html_e( ' Send notification when Save', 'stionic-core' ) . '</label></p>';

		// Checkbox send notification only follow
		if ( $post->post_type == 'post' ) {
			echo '<p>├─<label for="stionic-notification-follow">
                <input id="stionic-notification-follow" type="checkbox" name="_notification_follow" ' . ( ! empty( $value['_stionic_future_notification'][0]['follow'] ) ? 'checked' : '' ) . ' />';
			esc_html_e( ' Only send to follower Categories', 'stionic-core' ) . '</label></p>';
		}

		// Message format
		echo '<p><input type="text" name="_notification_format" class="components-text-control__input" value="' . esc_attr( @$value['_stionic_future_notification'][0]['format'] ) . '" placeholder="Message format" /></p>';
		echo '<p class="description">' . esc_html( __( 'Use {{title}} for placeholder title' ) ) . '</p>';

		echo '<p><b>' . esc_html( __( 'Rewards ads', 'stionic-core' ) ) . '</b></p>';
		// Show after how many views
		echo '<input type="number" min="1" name="_stionic_required_rewards_ads" class="components-text-control__input" value="' . esc_attr( @$value['_stionic_required_rewards_ads'][0] ) . '" placeholder="Rewards after" />';
		echo '<p class="description">' . esc_html( __( 'After how many views the post will required Rewards ads' ) ) . '</p>';
	}
	function stionic_save_post( $postid, $post ) {
		// Verify nonce
		if ( empty( $_POST['stionic_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['stionic_metabox_nonce'], basename( __FILE__ ) ) ) {
			return false;
		}
		/* check if this is an autosave */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}
		// if not post or page
		if ( empty( $postid ) || ! isset( $_POST['post_type'] ) || $_POST['post_type'] != 'post' && $_POST['post_type'] != 'page' ) {
			return false;
		}
		// if current user can't edit this post
		if ( $_POST['post_type'] == 'post' && ! current_user_can( 'edit_post', $postid ) ) {
			return;
		}
		// if current user can't edit this page
		if ( $_POST['post_type'] == 'page' && ! current_user_can( 'edit_page', $postid ) ) {
			return;
		}
		// check if the custom field is submitted (checkboxes that aren't marked, aren't submitted)
		// update post type
		// check value
		if ( isset( $_POST['_stionic_post_format'] ) ) {
			$stionic_post_format = in_array( $_POST['_stionic_post_format'], array( 'play', 'image' ) ) ? sanitize_text_field( $_POST['_stionic_post_format'] ) : 'standard';
			/* store the value in the database */
			if ( $stionic_post_format == 'standard' ) {
				delete_post_meta( $postid, '_stionic_post_format' );
			} else {
				update_post_meta( $postid, '_stionic_post_format', $stionic_post_format );
			}
		}
		// update featured top detail
		if ( isset( $_POST['_stionic_exclude_featured'] ) ) {
			/* store the value in the database */
			update_post_meta( $postid, '_stionic_exclude_featured', 1 );
		} else {
			/* not marked? delete the value in the database */
			delete_post_meta( $postid, '_stionic_exclude_featured' );
		}
		// update show in application
		if ( isset( $_POST['_show_in_application'] ) ) {
			/* store the value in the database */
			update_post_meta( $postid, '_show_in_application', 1 );
		} else {
			/* not marked? delete the value in the database */
			delete_post_meta( $postid, '_show_in_application' );
		}
		// send notification
		if ( ! empty( $_POST['_notification'] ) && isset( $post->post_status ) ) {
			if ( $post->post_status == 'future' ) {
				$stionic_future_notification = array(
					'follow' => ! empty( $_POST['_notification_follow'] ),
					'format' => sanitize_text_field( @$_POST['_notification_format'] ),
				);
				/* store the value in the database */
				update_post_meta( $postid, '_stionic_future_notification', $stionic_future_notification );
			} elseif ( $this->checkNotification( $post ) ) {
				// prepare params
				$title      = get_the_title( $postid );
				$data       = array(
					'link' => get_permalink( $postid ),
					'type' => sanitize_text_field( @$_POST['post_type'] ),
					'id'   => $postid,
				);
				$categories = ( ! empty( $_POST['_notification_follow'] ) ) ? get_the_category( $postid ) : null;
				$format     = sanitize_text_field( @$_POST['_notification_format'] );
				$result     = $this->sendNotification( $title, $data, $categories, $format );
				/* not marked? delete the value in the database */
				delete_post_meta( $postid, '_stionic_future_notification' );
			}
		} elseif ( empty( $_POST['_notification'] ) ) {
			/* not marked? delete the value in the database */
			delete_post_meta( $postid, '_stionic_future_notification' );
		}
		// update required rewards ads
		if ( isset( $_POST['_stionic_required_rewards_ads'] ) && intval( $_POST['_stionic_required_rewards_ads'] ) > 0 ) {
			/* store the value in the database */
			update_post_meta( $postid, '_stionic_required_rewards_ads', intval( $_POST['_stionic_required_rewards_ads'] ) );
		} else {
			/* not marked? delete the value in the database */
			delete_post_meta( $postid, '_stionic_required_rewards_ads' );
		}
	}
	function stionic_notification_filter( $filter, $post ) {
		if ( isset( $post->post_status ) && $post->post_status == 'publish' ) {
			$filter = true;
		}
		return $filter;
	}
	function stionic_future_post( $postid ) {
		$stionic_future_notification = get_post_meta( $postid, '_stionic_future_notification', true );
		if ( ! empty( $stionic_future_notification ) && $this->checkNotification( get_post( $postid ) ) ) {
			// prepare params
			$title      = get_the_title( $postid );
			$data       = array(
				'link' => get_permalink( $postid ),
				'type' => get_post_type( $postid ),
				'id'   => $postid,
			);
			$categories = ( ! empty( $stionic_future_notification['follow'] ) ) ? get_the_category( $postid ) : null;
			$format     = @$stionic_future_notification['format'];
			$result     = $this->sendNotification( $title, $data, $categories, $format );
			/* not marked? delete the value in the database */
			delete_post_meta( $postid, '_stionic_future_notification' );
		}
	}
	function checkNotification( $post ) {
		$filter = apply_filters( 'stionic_notification_filter', false, $post );
		return (bool) $filter;
	}
	function sendNotification( $content, $data, $categories = null, $format = null ) {
		// get settings
		$onesignal = get_option( 'stionic_onesignal' );
		if ( empty( $onesignal['onesignal_app_id'] ) || empty( $onesignal['rest_api_key'] ) ) {
			return null;
		}

		if ( empty( $format ) ) {
			$format = @$onesignal['message_format'];
		}
		if ( empty( $format ) ) {
			$format = '{{title}}';
		}

		$content = wp_unslash( $this->decode_entities( str_replace( '{{title}}', $content, $format ) ) );

		// prepare notification
		$fields = array(
			'app_id'         => $onesignal['onesignal_app_id'],
			'data'           => $data,
			'contents'       => array( 'en' => $content ),
			'isIos'          => true,
			'ios_badgeType'  => 'Increase',
			'ios_badgeCount' => 1,
			'isAndroid'      => true,
			'isWP'           => true,
			'isWP_WNS'       => true,
		);

		$default_params = array();
		if ( ! empty( $onesignal['default_params'] ) ) {
			$default_params = Stionic_Functions::decode_json_option( $onesignal['default_params'] );
			if ( is_array( $default_params['data'] ) && is_array( $fields['data'] ) && ! empty( $default_params['data'] ) ) {
				$fields['data'] = array_merge( $default_params['data'], $fields['data'] );
			}
			$fields = array_merge( $default_params, $fields );
		}

		if ( ! empty( $onesignal['include_player_ids'] ) ) {
			$fields['include_player_ids'] = explode( ',', $onesignal['include_player_ids'] );
		} elseif ( ! empty( $categories ) && is_array( $categories ) ) {
			$filters = array();
			$or      = array( 'operator' => 'OR' );
			foreach ( $categories as $category ) {
				if ( empty( $category ) ) {
					continue;
				}
				$now = array(
					'field'    => 'tag',
					'key'      => $category->cat_ID,
					'relation' => 'exists',
				);
				array_push( $filters, $now );
				if ( ! empty( $default_params['customTag'] ) ) {
					array_push( $filter, $default_params['customTag'] );
				}
				array_push( $filters, $or );
			}
			array_pop( $filters );
			$fields['filters'] = $filters;
		} elseif ( ! isset( $fields['included_segments'] ) ) {
			$fields['included_segments'] = array( 'All' );
		}
		$fields = apply_filters( 'stionic_notification_fields', $fields );

		// request API
		$url      = 'https://onesignal.com/api/v1/notifications';
		$args     = array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type'  => 'application/json; charset=utf-8',
				'Authorization' => 'Basic ' . $onesignal['rest_api_key'],
			),
			'body'    => wp_json_encode( $fields ),
		);
		$response = wp_remote_request( $url, $args );

		if ( ! empty( $onesignal['error_log'] ) ) {
			error_log( wp_json_encode( $response ) );
		}

		return $response;
	}
	// function from OneSignal offical plugin
	public static function decode_entities( $string ) {
		$HTML_ENTITY_DECODE_FLAGS = ENT_QUOTES;
		if ( defined( 'ENT_HTML401' ) ) {
			$HTML_ENTITY_DECODE_FLAGS = ENT_HTML401 | $HTML_ENTITY_DECODE_FLAGS;
		}
		return html_entity_decode( str_replace( '&apos;', "'", $string ), $HTML_ENTITY_DECODE_FLAGS, 'UTF-8' );
	}
}
new Stionic_Admin_Metaboxes();
