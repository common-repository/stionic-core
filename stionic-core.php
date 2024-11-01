<?php
/**
 * @package noncheat\stionic-core
 * Plugin Name: Stionic Core
 * Plugin URI: https://noncheat.com/category/plugins/stionic-core/
 * Description: Extending the REST API for WordPress application
 * Version: 1.0.28
 * Author: Noncheat
 * Author URI: https://noncheat.com
 * Requires at least: 4.7
 * Tested up to: 5.9.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Stionic {
	public function __construct() {
		// define
		if ( ! defined( 'STIONIC_HOME' ) ) {
			define( 'STIONIC_HOME', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'STIONIC_ASSETS' ) ) {
			define( 'STIONIC_ASSETS', plugins_url( '', __FILE__ ) );
		}
		if ( ! defined( 'STIONIC_ASSETS_VERSION' ) ) {
			define( 'STIONIC_ASSETS_VERSION', '1.0.11' );
		}
		// require classes
		require_once 'classes/class-stionic-functions.php';
		// require admin
		require_once 'admin/class-stionic-admin.php';
		// require hooks
		require_once 'hooks/class-stionic-ads.php';
		require_once 'hooks/class-stionic-header.php';
		// require endpoints
		require_once 'endpoints/class-stionic-config.php';
		require_once 'endpoints/class-stionic-categories.php';
		require_once 'endpoints/class-stionic-posts.php';
		require_once 'endpoints/class-stionic-comments.php';
		require_once 'endpoints/class-stionic-pages.php';
		require_once 'endpoints/class-stionic-tools.php';
		// require includes
		require_once 'includes/class-stionic-posts.php';
	}
}
new Stionic();
