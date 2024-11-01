<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Admin {
	protected $slug;
	protected $display_name;
	protected $settings_name;
	protected $option_settings;
	protected $categories_name;
	protected $option_categories;
	protected $media_name;
	protected $option_media;
	protected $deeplinks_name;
	protected $option_deeplinks;
	protected $onesignal_name;

	public function __construct() {
		$this->slug              = 'stionic';
		$this->display_name      = 'Stionic';
		$this->settings_name     = 'stionic_settings';
		$this->option_settings   = get_option( $this->settings_name );
		$this->categories_name   = 'stionic_categories';
		$this->option_categories = get_option( $this->categories_name );
		$this->media_name        = 'stionic_media';
		$this->option_media      = get_option( $this->media_name );
		$this->deeplinks_name    = 'stionic_deeplinks';
		$this->option_deeplinks  = get_option( $this->deeplinks_name );
		$this->onesignal_name    = 'stionic_onesignal';
		$this->option_onesignal  = get_option( $this->onesignal_name );

		// require metaboxes
		require_once 'class-stionic-metaboxes.php';
		// require list posts
		require_once 'class-stionic-list-posts.php';
		// Pre dispatch
		add_filter( 'rest_pre_dispatch', array( $this, 'rest_pre_dispatch' ), 10, 3 );
		// Admin init
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		// Admin menu
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		// Add Footer
		add_action( 'wp_footer', array( $this, 'wp_footer' ) );
		// Default settings
		if ( $this->option_settings == false ) {
			$this->option_settings = array(
				'request_timeout' => 30000,
				'root_endpoint'   => get_option( 'siteurl' ) . '/wp-json/',
				'ads_after'       => 5,
			);
			update_option( $this->settings_name, $this->option_settings );
		}
		// Default media
		if ( $this->option_media == false ) {
			$this->option_media = array(
				'stionic_thumbnail' => array( 400, 225 ),
				'stionic_square'    => array( 100, 100 ),
			);
			update_option( $this->media_name, $this->option_media );
		}
		// add image size
		$this->add_stionic_image_size();
	}
	function rest_pre_dispatch( $result, $server, $request ) {
		// die all endpoint exclude m_config if maintenance
		$is_config = '/wp/v2/m_config' === $request->get_route();
		if ( ! empty( $this->option_settings['maintenance'] ) && ! $is_config ) {
			return new WP_Error( 'maintenance', __( 'Maintenance' ), array( 'status' => 404 ) );
		}
	}
	function admin_init() {
		// Save taxonomy order
		add_action( 'wp_ajax_update_taxonomy_order', array( $this, 'save_taxonomy_order' ) );
	}
	function admin_menu() {
		// add to Settings menu
		add_menu_page(
			$this->display_name,
			$this->display_name,
			'manage_options',
			$this->slug,
			array( $this, 'create_menu_stionic' ),
			'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOS4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAzMiAzMiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMzIgMzI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiMyNzJBNkI7fQ0KCS5zdDF7ZmlsbDojRkVDNDBEO30NCgkuc3Qye2ZpbGw6dXJsKCNYTUxJRF8xOV8pO30NCgkuc3Qze2ZpbGw6IzY1NjZBRTt9DQo8L3N0eWxlPg0KPGcgaWQ9IlhNTElEXzE0XyI+DQoJPHBhdGggaWQ9IlhNTElEXzE3XyIgY2xhc3M9InN0MCIgZD0iTTAsMzAuNGM3LjYsMiwyNC42LDIsMjYuNC0xLjljMS41LTMuMS0zLjctOC4yLTYuOC0xMS43Yy0wLjMtMC4zLTIsMC4yLTEuNywwLjUNCgkJYzMuNiwzLjksNC40LDYuNywzLjIsOS40Yy0xLjQsMy4yLTUuMyw1LjctMTkuNywzLjYiLz4NCgk8cGF0aCBpZD0iWE1MSURfMzVfIiBjbGFzcz0ic3QxIiBkPSJNMjkuOSwxNGMtMC4xLDAuMi0wLjIsMC4zLTAuNCwwLjRjMC45LDEuNiwxLjYsMy44LDEuMSw1LjFjLTEuMSwzLTEwLjEsNC4yLTE1LjQsMg0KCQljLTctMi45LTEyLjQtNi43LTEwLjktMTAuNmMxLjQtMy45LDkuMS01LDE2LjItMi41YzIuNywxLDUsMi4zLDYuOCwzLjdjMC4xLTAuMSwwLjItMC4xLDAuMy0wLjFjLTEuOS0xLjUtNC4zLTIuOS03LTMuOQ0KCQlDMTIuOSw1LjMsNS40LDYuNSwzLjksMTAuN0MyLjMsMTUsNy4zLDIwLjcsMTUsMjMuNXMxNS4yLDEuNywxNi44LTIuNkMzMi41LDE5LjEsMzEuNCwxNi4yLDI5LjksMTR6Ii8+DQoJPGxpbmVhckdyYWRpZW50IGlkPSJYTUxJRF8xOV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iMjUuNzU1NCIgeTE9IjEyLjYyMTgiIHgyPSIzMC40MDkxIiB5Mj0iMTIuNjIxOCI+DQoJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiNGRkU3M0MiLz4NCgkJPHN0b3AgIG9mZnNldD0iOC43MTA2MTVlLTAwMiIgc3R5bGU9InN0b3AtY29sb3I6I0ZGRDQzRiIvPg0KCQk8c3RvcCAgb2Zmc2V0PSIwLjE5ODkiIHN0eWxlPSJzdG9wLWNvbG9yOiNGQkMxNDEiLz4NCgkJPHN0b3AgIG9mZnNldD0iMC4zMTMyIiBzdHlsZT0ic3RvcC1jb2xvcjojRjhCMjQxIi8+DQoJCTxzdG9wICBvZmZzZXQ9IjAuNDMwNiIgc3R5bGU9InN0b3AtY29sb3I6I0Y3QUE0MSIvPg0KCQk8c3RvcCAgb2Zmc2V0PSIwLjU1NTYiIHN0eWxlPSJzdG9wLWNvbG9yOiNGNkE3NDEiLz4NCgkJPHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6I0VFODA0MCIvPg0KCTwvbGluZWFyR3JhZGllbnQ+DQoJPGNpcmNsZSBpZD0iWE1MSURfMTZfIiBjbGFzcz0ic3QyIiBjeD0iMjguMSIgY3k9IjEyLjYiIHI9IjIuMyIvPg0KCTxwYXRoIGlkPSJYTUxJRF8xNV8iIGNsYXNzPSJzdDMiIGQ9Ik0yNS45LDAuM2MtNy4yLTAuNC0xNy4xLDEuNC0xNyw2LjljMC4xLDQuNyw3LDguOCw5LjgsMTAuOWMwLDAsMC45LDAuNiwwLjksMC42DQoJCWMyLjksMiwyLjgsMiwzLjgsMi4zYzAuMiwwLjEtMi41LTIuOS0yLjYtM2MtNC40LTUuNC01LjEtNy45LTUtMTAuMWMwLjItNC40LDIuNy02LjUsNy41LTcuMSIvPg0KPC9nPg0KPC9zdmc+DQo='
		);
		add_submenu_page( $this->slug, __( 'General' ) . ' - ' . $this->display_name, __( 'General' ), 'manage_options', $this->slug, array( $this, 'create_menu_stionic' ) );
		add_submenu_page( $this->slug, __( 'Categories' ) . ' - ' . $this->display_name, __( 'Categories' ), 'manage_options', $this->slug . '-categories', array( $this, 'create_menu_stionic_categories' ) );
		add_submenu_page( $this->slug, __( 'Media' ) . ' - ' . $this->display_name, __( 'Media' ), 'manage_options', $this->slug . '-media', array( $this, 'create_menu_stionic_media' ) );
		add_submenu_page( $this->slug, __( 'DeepLinks', 'stionic-core' ) . ' - ' . $this->display_name, __( 'DeepLinks', 'stionic-core' ), 'manage_options', $this->slug . '-deeplinks', array( $this, 'create_menu_stionic_deeplinks' ) );
		add_submenu_page( $this->slug, __( 'OneSignal', 'stionic-core' ) . ' - ' . $this->display_name, __( 'OneSignal', 'stionic-core' ), 'manage_options', $this->slug . '-onesignal', array( $this, 'create_menu_stionic_onesignal' ) );
	}
	function create_menu_stionic() {
		// Add javascript
		wp_register_script( 'stionic-general-script', STIONIC_ASSETS . '/admin/js/general.js?v=' . STIONIC_ASSETS_VERSION );
		wp_enqueue_script( 'stionic-general-script' );
		// Add style
		wp_register_style( 'stionic-general-style', STIONIC_ASSETS . '/admin/css/general.css?v=' . STIONIC_ASSETS_VERSION );
		wp_enqueue_style( 'stionic-general-style' );
		// requrie templates
		require_once 'templates/general.php';
	}
	function create_menu_stionic_categories() {
		// Add javascript
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_register_script( 'stionic-categories-script', STIONIC_ASSETS . '/admin/js/categories.js?v=' . STIONIC_ASSETS_VERSION );
		wp_enqueue_script( 'stionic-categories-script' );
		wp_localize_script(
			'stionic-categories-script',
			$this->display_name,
			array(
				// generate a nonce with a unique ID "stionic-order-category-nonce"
				'nonce' => wp_create_nonce( 'stionic-order-category-nonce' ),
			)
		);
		// Add style
		wp_register_style( 'stionic-categories-style', STIONIC_ASSETS . '/admin/css/categories.css?v=' . STIONIC_ASSETS_VERSION );
		wp_enqueue_style( 'stionic-categories-style' );
		// require templates
		require_once 'class-stionic-walker.php';
		require_once 'templates/functions.php';
		require_once 'templates/categories.php';
	}
	function create_menu_stionic_media() {
		require_once 'templates/media.php';
	}
	function create_menu_stionic_deeplinks() {
		wp_register_script( 'stionic-deeplinks-script', STIONIC_ASSETS . '/admin/js/deeplinks.js?v=' . STIONIC_ASSETS_VERSION );
		wp_enqueue_script( 'stionic-deeplinks-script' );
		// DeepLinks
		require_once 'templates/deeplinks.php';
	}
	function create_menu_stionic_onesignal() {
		require_once 'templates/onesignal.php';
	}
	function add_stionic_image_size() {
		if ( is_array( $this->option_media ) ) {
			foreach ( array( 'stionic_thumbnail', 'stionic_square' ) as $key ) {
				add_image_size( $key, $this->option_media[ $key ][0], $this->option_media[ $key ][1], true );
			}
		}
	}
	function getBrowser() {
		$u_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ) : '';
		$browser = 'Browser';
		$name    = 'browser';

		if ( preg_match( '/Edg/i', $u_agent ) ) {
			$browser = 'Edge';
			$name    = 'edge';
		} elseif ( preg_match( '/Firefox/i', $u_agent ) ) {
			$browser = 'Firefox';
			$name    = 'firefox';
		} elseif ( preg_match( '/OPR/i', $u_agent ) || preg_match( '/Opera/i', $u_agent ) ) {
			$browser = 'Opera';
			$name    = 'opera';
		} elseif ( preg_match( '/UCBrowser/i', $u_agent ) ) {
			$browser = 'UC Browser';
			$name    = 'ucbrowser';
		} elseif ( preg_match( '/MiuiBrowser/i', $u_agent ) ) {
			$browser = 'Miui Browser';
			$name    = 'miuibrowser';
		} elseif ( preg_match( '/YaBrowser/i', $u_agent ) ) {
			$browser = 'Yandex';
			$name    = 'yabrowser';
		} elseif ( preg_match( '/Chrome/i', $u_agent ) ) {
			$browser = 'Chrome';
			$name    = 'chrome';
		} elseif ( preg_match( '/MSIE/i', $u_agent ) || preg_match( '/Trident/i', $u_agent ) ) {
			$browser = 'Internet Explorer';
			$name    = 'msie';
		} elseif ( preg_match( '/Safari/i', $u_agent ) && ! preg_match( '/Android/i', $u_agent ) ) {
			$browser = 'Safari';
			$name    = 'safari';
		} elseif ( preg_match( '/Netscape/i', $u_agent ) ) {
			$browser = 'Netscape';
			$name    = 'netscape';
		}

		return array(
			'browser' => $browser,
			'name'    => $name,
			'icon'    => STIONIC_ASSETS . "/assets/img/icon-$name.png?v=" . STIONIC_ASSETS_VERSION,
		);
	}
	function wp_footer() {
		if ( empty( $_COOKIE['stionic_hide_deeplinks'] ) && wp_is_mobile() && ! empty( $this->option_deeplinks['button_deeplinks'] ) && ! empty( $this->option_deeplinks['url_scheme'] ) ) {
			wp_register_script( 'stionic-footer-script', STIONIC_ASSETS . '/assets/js/footer.js?v=' . STIONIC_ASSETS_VERSION, array( 'jquery' ) );
			wp_enqueue_script( 'stionic-footer-script' );
			wp_register_style( 'stionic-footer-style', STIONIC_ASSETS . '/assets/css/footer.css?v=' . STIONIC_ASSETS_VERSION );
			wp_enqueue_style( 'stionic-footer-style' );
			$browser = apply_filters( 'stionic_deeplink_browser', $this->getBrowser() );
			?>
				<div class="panel-open-application<?php echo esc_attr( ' browser-' . @$browser['name'] ); ?>">
					<input type="hidden" id="application_apple_id" value="<?php echo esc_attr( @$this->option_settings['apple_id'] ); ?>" />
					<input type="hidden" id="application_package" value="<?php echo esc_attr( @$this->option_settings['package'] ); ?>" />
					<div class="open-application-content">
						<div class="open-application-title"><?php esc_html_e( 'Continue in...', 'stionic-core' ); ?></div>
						<div class="open-application-button">
							<?php if ( ! empty( $this->option_deeplinks['button_icon'] ) ) { ?>
							<img src="<?php echo esc_attr( $this->option_deeplinks['button_icon'] ); ?>" />
							<?php } ?>
							<span><?php echo esc_html( empty( $this->option_deeplinks['button_text'] ) ? __( 'Application', 'stionic-core' ) : wp_unslash( $this->option_deeplinks['button_text'] ) ); ?></span>
							<button id="open_application" 
							<?php
							if ( ! empty( $this->option_deeplinks['auto_open'] ) || isset( $_GET['stionicDeeplinks'] ) ) {
								echo 'data-auto=1';}
							?>
							data-scheme="<?php echo esc_attr( $this->option_deeplinks['url_scheme'] ); ?>">
								<span><?php esc_html_e( 'Open', 'stionic-core' ); ?></span>
							</button>
						</div>
						<div class="open-application-button is-secondary">
							<?php if ( ! empty( $this->option_deeplinks['button_icon'] ) ) { ?>
							<img src="<?php echo esc_attr( @$browser['icon'] ); ?>" />
							<?php } ?>
							<span><?php echo esc_html( @$browser['browser'] ); ?></span>
							<button id="close_application" data-second="<?php echo esc_attr( @$this->option_deeplinks['wait'] ); ?>"><?php esc_html_e( 'Continue', 'stionic-core' ); ?></button>
						</div>
					</div>
				</div>
			<?php
		}
	}
	function save_taxonomy_order() {
		global $wpdb;
		$unserialised_data = ! empty( $_POST['order'] ) && is_string( $_POST['order'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['order'] ) ) ) : array();
		$hideInApp         = ! empty( $_POST['hide'] ) && is_string( $_POST['hide'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['hide'] ) ) ) : array();
		// check to see if the submitted nonce matches
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'stionic-order-category-nonce' ) ) {
			die( esc_html( __( 'Invalid nonce', 'stionic-core' ) ) );
		}
		if ( ! empty( $unserialised_data ) && is_array( $unserialised_data ) ) {
			foreach ( $unserialised_data as $key => $values ) {
				$wpdb->update( $wpdb->terms, array( 'term_group' => ( $key + 1 ) ), array( 'term_id' => intval( trim( str_replace( 'item_', '', $values ) ) ) ) );
			}
		}
		if ( is_array( $hideInApp ) ) {
			update_option( 'stionic-categories-hide', $hideInApp );
		}
		$option_categories = array(
			'show_all'   => filter_var( @$_POST['show_all'], FILTER_VALIDATE_BOOLEAN ),
			'check_show' => filter_var( @$_POST['check_show'], FILTER_VALIDATE_BOOLEAN ),
		);
		update_option( $this->categories_name, $option_categories );
		die( wp_json_encode( $option_categories ) );
	}
}
new Stionic_Admin();
