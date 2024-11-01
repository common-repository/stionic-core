<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	// Update settings
if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'stionic-settings-nonce' ) && isset( $_POST['save'] ) ) {
	// Get data
	$data                       = array();
	$data['onesignal_app_id']   = isset( $_POST['onesignal_app_id']) ? sanitize_text_field( $_POST['onesignal_app_id'] ) : null;
	$data['rest_api_key']       = isset( $_POST['rest_api_key']) ? sanitize_text_field( $_POST['rest_api_key'] ) : null;
	$data['message_format']     = isset( $_POST['message_format']) ? sanitize_text_field( $_POST['message_format'] ) : null;
	$data['include_player_ids'] = isset( $_POST['include_player_ids']) ? sanitize_text_field( $_POST['include_player_ids'] ) : null;
	$data['default_params']     = isset( $_POST['default_params']) ? sanitize_textarea_field( $_POST['default_params'] ) : null;
	$data['error_log']          = ! empty( $_POST['error_log'] );
	$data['default_send']       = ! empty( $_POST['default_send'] );
	// Update
	if ( update_option( $this->onesignal_name, array_filter( $data ) ) ) {
		echo '<div class="notice updated is-dismissible"><p>' . esc_html__( 'Saved' ) . '</p></div>';
	}
} else {
	$data = $this->option_onesignal; // Load data from database
}
?>
<div class="wrap stionic-onesignal">
	<h1><?php echo esc_html( __( 'OneSignal', 'stionic-core' ) . ' - ' . $this->display_name ); ?></h1>
	<form method="POST">
		<p class="description"><?php esc_html_e( 'Configure OneSignal to send notifications when saving posts.', 'stionic-core' ); ?></p>
		<p class="description"><?php echo wp_kses( sprintf( __( 'To get Keys and IDs, you need login to <a href="%s">OneSignal</a>. Then select your app -> App Settings -> Keys & IDs.', 'stionic-core' ), 'https://onesignal.com' ), array( 'a' => array( 'href' => array() ) ) ); ?></p>
		<input type="hidden" name="save" value="true" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'stionic-settings-nonce' ) ); ?>" />
		<table class="form-table">
		<tbody>
			<tr>
				<th>OneSignal APP ID *</th>
				<td><input type="text" name="onesignal_app_id" value="<?php echo esc_attr( @$data['onesignal_app_id'] ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th>REST API KEY *</th>
				<td><input type="text" name="rest_api_key" value="<?php echo esc_attr( @$data['rest_api_key'] ); ?>" class="regular-text" /></td>
			</tr>
			<tr>
				<th>Message format</th>
				<td>
					<input type="text" name="message_format" value="<?php echo esc_attr( @$data['message_format'] ); ?>" class="regular-text" placeholder="{{title}}" />
					<p class="description"><?php esc_html_e( 'Use {{title}} for placeholder title', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Include Player IDs', 'stionic-core' ); ?></th>
				<td>
					<textarea rows="5" name="include_player_ids" class="regular-text"><?php echo esc_attr( @$data['include_player_ids'] ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Player Id get in the OneSignal Dashboard, separated by ","', 'stionic-core' ); ?></p>
					<p class="description"><?php esc_html_e( 'Used for testing, when publishing need to delete this field.', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Default send params', 'stionic-core' ); ?></th>
				<td>
					<textarea rows="5" name="default_params" class="regular-text" placeholder="<?php esc_attr_e( 'Put JSON data here', 'stionic-core' ); ?>"><?php
						if ( ! empty( $data['default_params'] ) ) {
							echo esc_textarea( Stionic_Functions::esc_json_option( $data['default_params'] ) );
						}
					?></textarea>
					<p class="description"><?php esc_html_e( 'Developers use this field to set default params when send notifications', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Send when Save', 'stionic-core' ); ?></th>
				<td>
				<label>
					<input type="checkbox" name="default_send" <?php checked( @$data['default_send'] ); ?> />
					<span><?php esc_html_e( 'Check for default Send notification when Save', 'stionic-core' ); ?></span>
				</label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Error log', 'stionic-core' ); ?></th>
				<td>
				<label>
					<input type="checkbox" name="error_log" <?php checked( @$data['error_log'] ); ?> />
					<span><?php esc_html_e( 'Check for write error_log response of OneSignal', 'stionic-core' ); ?></span>
				</label>
				</td>
			</tr>
		</tbody>
		</table>
		<button class="button button-primary button-large"><?php esc_html_e( 'Update' ); ?></button>
	</form>
</div>
