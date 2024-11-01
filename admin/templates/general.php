<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	// Update settings
if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'stionic-settings-nonce' ) && isset( $_POST['save'] ) ) {
	// Get data
	$data = array();
	if ( ! empty( $_POST['maintenance_check'] && isset( $_POST['maintenance_content'] ) ) ) {
		$data['maintenance'] = sanitize_text_field( $_POST['maintenance_content'] );
	} else {
		$data['maintenance'] = false;
	}
	$data['is_testing_ads']       = ! empty( $_POST['is_testing_ads'] );
	$data['android_banner']       = isset( $_POST['android_banner'] ) ? sanitize_text_field( $_POST['android_banner'] ) : null;
	$data['ios_banner']           = isset( $_POST['ios_banner'] ) ? sanitize_text_field( $_POST['ios_banner'] ) : null;
	$data['android_interstitial'] = isset( $_POST['android_interstitial'] ) ? sanitize_text_field( $_POST['android_interstitial'] ) : null;
	$data['ios_interstitial']     = isset( $_POST['ios_interstitial'] ) ? sanitize_text_field( $_POST['ios_interstitial'] ) : null;
	$data['android_rewards']      = isset( $_POST['android_rewards'] ) ? sanitize_text_field( $_POST['android_rewards'] ) : null;
	$data['ios_rewards']          = isset( $_POST['ios_rewards'] ) ? sanitize_text_field( $_POST['ios_rewards'] ) : null;
	$data['ads_after']            = isset( $_POST['ads_after'] ) ? intval( $_POST['ads_after'] ) : 0;
	if ( isset( $_POST['ads_default_reward'] ) && intval( $_POST['ads_default_reward'] ) > 0 ) {
		$data['ads_default_reward'] = intval( $_POST['ads_default_reward'] );
	}
	$data['google_analytics'] = isset( $_POST['google_analytics'] ) ? sanitize_text_field( $_POST['google_analytics'] ) : null;
	$data['package']          = isset( $_POST['package'] ) ? sanitize_text_field( $_POST['package'] ) : null;
	$data['apple_id']         = isset( $_POST['apple_id'] ) ? sanitize_text_field( $_POST['apple_id'] ) : null;
	$data['request_timeout']  = isset( $_POST['request_timeout'] ) ? intval( $_POST['request_timeout'] ) : 0;
	if ( $data['request_timeout'] < 15000 ) {
		$data['request_timeout'] = 15000;
	}
	$data['root_endpoint'] = isset( $_POST['root_endpoint'] ) ? sanitize_text_field( $_POST['root_endpoint'] ) : null;
	$data['share_android'] = isset( $_POST['share_android'] ) ? sanitize_textarea_field( $_POST['share_android'] ) : null;
	$data['share_ios']     = isset( $_POST['share_ios'] ) ? sanitize_textarea_field( $_POST['share_ios'] ) : null;
	$data['version']       = isset( $_POST['version'] ) ? sanitize_text_field( $_POST['version'] ) : null;
	$data['version_ios']   = isset( $_POST['version_ios'] ) ? sanitize_text_field( $_POST['version_ios'] ) : null;
	if ( isset( $_POST['allow_origin'] ) ) {
		$data['allow_origin'] = implode( PHP_EOL, array_filter( array_map( 'trim', preg_split( "/\\r\\n|\\r|\\n/", sanitize_textarea_field( $_POST['allow_origin'] ) ) ) ) );
	}
	$data['extended'] = isset( $_POST['extended'] ) ? sanitize_textarea_field( $_POST['extended'] ) : null;
	// Update
	if ( update_option( $this->settings_name, array_filter( $data ) ) ) {
		echo '<div class="notice updated is-dismissible"><p>' . esc_html__( 'Saved' ) . '</p></div>';
	}
} else {
	$data = $this->option_settings; // Load data from database
}
if ( empty( $data['package'] ) && empty( $data['apple_id'] ) ) {
	echo '<div class="notice error is-dismissible"><p><a href="#application_setting">' . esc_html__( 'You have not configured Application', 'stionic-core' ) . '</a></p></div>';
}
if ( empty( $data['root_endpoint'] ) ) {
	echo '<div class="notice error is-dismissible"><p><a href="#root_endpoint_setting">' . esc_html__( 'You have not configured Root Endpoint', 'stionic-core' ) . '</a></p></div>';
}
?>
<div class="wrap stionic-general">
	<h1><?php echo esc_html( __( 'General' ) . ' - ' . $this->display_name ); ?></h1>
	<form method="POST">
		<p class="description"><?php echo wp_kses( sprintf( __( 'We are selling mobile application themes at <a href="%s">Homepage</a>', 'stionic-core' ), 'https://noncheat.com/' ), array( 'a' => array( 'href' => array() ) ) ); ?></p>
		<p class="description"><?php echo wp_kses( sprintf( __( 'You can <a href="%s">Request a Demo</a> for your website (free)', 'stionic-core' ), 'https://noncheat.com/request-demo/' ), array( 'a' => array( 'href' => array() ) ) ); ?></p>
		<input type="hidden" name="save" value="true" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'stionic-settings-nonce' ) ); ?>" />
		<table class="form-table">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Maintenance' ); ?></th>
				<td class="has-checkbox">
				<label>
					<input type="checkbox" name="maintenance_check" <?php echo ( ! empty( $data['maintenance'] ) ? 'checked' : null ); ?> />
					<input type="text" name="maintenance_content" value="<?php echo esc_attr( @$data['maintenance'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Content' ); ?>" />
				</label>
				</td>
			</tr>
			<tr>
				<th>Google AdMob</th>
				<td>
					<p class="description"><?php esc_html_e( 'Banner Android - iOS', 'stionic-core' ); ?></p>
					<input type="text" name="android_banner" value="<?php echo esc_attr( @$data['android_banner'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Banner Unit ID Android', 'stionic-core' ); ?>" />
					<input type="text" name="ios_banner" value="<?php echo esc_attr( @$data['ios_banner'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Banner Unit ID iOS', 'stionic-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Interstitial Android - iOS', 'stionic-core' ); ?></p>
					<input type="text" name="android_interstitial" value="<?php echo esc_attr( @$data['android_interstitial'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Interstitial Unit ID Android', 'stionic-core' ); ?>" />
					<input type="text" name="ios_interstitial" value="<?php echo esc_attr( @$data['ios_interstitial'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Interstitial Unit ID iOS', 'stionic-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Rewards Android - iOS', 'stionic-core' ); ?></p>
					<input type="text" name="android_rewards" value="<?php echo esc_attr( @$data['android_rewards'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Rewards Unit ID Android', 'stionic-core' ); ?>" />
					<input type="text" name="ios_rewards" value="<?php echo esc_attr( @$data['ios_rewards'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Rewards Unit ID iOS', 'stionic-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Testing Ads' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="is_testing_ads" <?php echo ( ! empty( $data['is_testing_ads'] ) ? 'checked' : null ); ?> />
						<span><?php esc_html_e( 'Check for show testing ads', 'stionic-core' ); ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Ads after', 'stionic-core' ); ?></th>
				<td>
					<input type="number" name="ads_after" value="<?php echo esc_attr( @$data['ads_after'] ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'After how many views the post will display Interstitial ads', 'stionic-core' ); ?></p>
					<input type="number" name="ads_default_reward" value="<?php echo esc_attr( @$data['ads_default_reward'] ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Default rewards ads value for posts', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>Google Analytics</th>
				<td><input type="text" name="google_analytics" value="<?php echo esc_attr( @$data['google_analytics'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Tracking ID', 'stionic-core' ); ?>" /></td>
			</tr>
			<tr id="application_setting">
				<th><?php esc_html_e( 'Application', 'stionic-core' ); ?> *</th>
				<td>
					<input type="text" name="package" value="<?php echo esc_attr( @$data['package'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Package name', 'stionic-core' ); ?>" />
					<input type="number" name="apple_id" value="<?php echo esc_attr( @$data['apple_id'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Apple App ID', 'stionic-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Request timeout', 'stionic-core' ); ?> *</th>
				<td>
					<input type="number" name="request_timeout" value="<?php echo esc_attr( @$data['request_timeout'] ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Maximum time per request in application (ms)', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr id="root_endpoint_setting">
				<th><?php esc_html_e( 'Root Endpoint', 'stionic-core' ); ?> *</th>
				<td>
					<input type="text" name="root_endpoint" value="<?php echo esc_attr( @$data['root_endpoint'] ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Example: http://example.com/wp-json/', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Share', 'stionic-core' ); ?></th>
				<td>
					<textarea rows="5" name="share_android" class="regular-text" placeholder="<?php esc_attr_e( 'Content share Android', 'stionic-core' ); ?>"><?php
					if ( isset( $data['share_android'] ) ) echo esc_textarea( wp_unslash( $data['share_android'] ) );
					?></textarea>
					<textarea rows="5" name="share_ios" class="regular-text" placeholder="<?php esc_attr_e( 'Content share iOS', 'stionic-core' ); ?>"><?php
					if ( isset( $data['share_ios'] ) ) echo esc_textarea( wp_unslash( $data['share_ios'] ) );
					?></textarea>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Version', 'stionic-core' ); ?></th>
				<td>
					<input type="text" name="version" value="<?php echo esc_attr( @$data['version'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Latest Android version', 'stionic-core' ); ?>" />
					<input type="text" name="version_ios" value="<?php echo esc_attr( @$data['version_ios'] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Latest iOS version', 'stionic-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Lower version should display a notification each time the app is opened', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Header allow Origin', 'stionic-core' ); ?></th>
				<td>
					<textarea rows="5" name="allow_origin" class="regular-text"><?php
					if ( isset( $data['allow_origin'] ) ) echo esc_textarea( wp_unslash( $data['allow_origin'] ) );
					?></textarea>
					<p class="description"><?php esc_html_e( 'List of domains you would like to allow, separated by line break', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Extended configuration', 'stionic-core' ); ?></th>
				<td>
					<textarea rows="5" name="extended" class="regular-text" placeholder="<?php esc_attr_e( 'Put JSON data here', 'stionic-core' ); ?>"><?php
					if ( ! empty( $data['extended'] ) ) echo esc_textarea( Stionic_Functions::esc_json_option( $data['extended'] ) );
					?></textarea>
					<p class="description"><?php esc_html_e( 'Developers use this field to add custom configurations', 'stionic-core' ); ?></p>
				</td>
			</tr>
		</tbody>
		</table>
		<button class="button button-primary button-large"><?php esc_html_e( 'Update' ); ?></button>
	</form>
</div>
