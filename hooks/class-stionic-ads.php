<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Stionic_Hook_Ads {
	public function __construct() {
		// add hooks
		add_filter( 'rest_prepare_post', array( $this, 'insert_post_ads' ), 10, 2 );
		add_filter( 'rest_prepare_page', array( $this, 'insert_post_ads' ), 10, 2 );
	}
	function insert_post_ads( $response, $post ) {
		// require insert post ads
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'insert-post-ads/insert-post-ads.php' ) ) {
			$content = $response->data['content']['rendered'];
			// require function
			require_once WP_PLUGIN_DIR . '/insert-post-ads/insert-post-ads.php';
			if ( class_exists( 'InsertPostAds' ) ) {
				$InsertPostAds = new InsertPostAds();
				if ( ! apply_filters( 'insert_post_ads_enabled', true ) ) {
					return $response;
				}
				// Settings
				$InsertPostAds->settings = get_option( $InsertPostAds->plugin->name );
				if ( ! is_array( $InsertPostAds->settings ) ) {
					return $response;
				}
				if ( count( $InsertPostAds->settings ) === 0 ) {
					return $response;
				}

				// Check if we are on a singular post type that's enabled
				foreach ( $InsertPostAds->settings as $postType => $enabled ) {
					if ( $enabled && $postType === $response->data['type'] ) {
						// Check the post hasn't disabled adverts
						$disable = get_post_meta( $post->ID, '_ipa_disable_ads', true );
						if ( ! $disable ) {
							$content = $InsertPostAds->insertAds( $content );
						}
					}
				}

				$response->data['content']['rendered'] = $content;
			}
		}
		return $response;
	}
}
new Stionic_Hook_Ads();
