<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	global $wpdb;
	// Update settings
if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'stionic-settings-nonce' ) && isset( $_POST['save'] ) ) {
	// Get data
	$data                = array();
	$data['url_scheme']  = isset( $_POST['url_scheme'] ) ? sanitize_text_field( $_POST['url_scheme'] ) : null;
	$data['button_icon'] = isset( $_POST['button_icon'] ) ? sanitize_text_field( $_POST['button_icon'] ) : null;
	$data['button_text'] = isset( $_POST['button_text'] ) ? sanitize_text_field( $_POST['button_text'] ) : null;
	$data['wait']        = isset( $_POST['wait'] ) ? intval( $_POST['wait'] ) : 0;
	if ( isset( $_POST['button_deeplinks'] ) ) {
		$data['button_deeplinks'] = (bool) $_POST['button_deeplinks'];
	}
	if ( isset( $_POST['auto_open'] ) ) {
		$data['auto_open'] = (bool) $_POST['auto_open'];
	}
	// Update
	if ( update_option( $this->deeplinks_name, array_filter( $data ) ) ) {
		echo '<div class="notice updated is-dismissible"><p>' . esc_html__( 'Saved' ) . '</p></div>';
	}
} else {
	$data = $this->option_deeplinks; // Load data from database
}
?>
<div class="wrap stionic-deeplinks">
	<h1><?php echo esc_html( __( 'DeepLinks', 'stionic-core' ) . ' - ' . $this->display_name ); ?></h1>
	<form method="POST">
		<p class="description"><?php esc_html_e( 'Setup Button to open application from website when using mobile.', 'stionic-core' ); ?></p>
		<p class="description"><?php echo wp_kses( sprintf( __( 'Make sure you have configured the Application at <a href="%s">General.</a>', 'stionic-core' ), '?page=stionic#application_setting' ), array( 'a' => array( 'href' => array() ) ) ); ?></p>
		<input type="hidden" name="save" value="true" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'stionic-settings-nonce' ) ); ?>" />
		<table class="form-table">
		<tbody>
			<tr>
				<th>URL SCHEME</th>
				<td>
					<input type="text" name="url_scheme" value="<?php echo esc_attr( @$data['url_scheme'] ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Button Icon URL', 'stionic-core' ); ?></th>
				<td>
					<input type="text" name="button_icon" value="<?php echo esc_attr( wp_unslash( @$data['button_icon'] ) ); ?>" class="regular-text" />
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Button text', 'stionic-core' ); ?></th>
				<td>
					<input type="text" name="button_text" value="<?php echo esc_attr( wp_unslash( @$data['button_text'] ) ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'Default: Application', 'stionic-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Wait after close', 'stionic-core' ); ?></th>
				<td>
				<label>
					<input type="number" name="wait" value="<?php echo esc_attr( @$data['wait'] ); ?>" class="regular-text" />
					<p><?php esc_html_e( 'How many seconds show popup again after click close', 'stionic-core' ); ?></p>
					<p><?php esc_html_e( 'If "-1" will show again after re-open browser', 'stionic-core' ); ?></p>
				</label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Button open application', 'stionic-core' ); ?></th>
				<td class="has-checkbox">
				<label>
					<input type="checkbox" name="button_deeplinks" <?php checked( @$data['button_deeplinks'] ); ?> />
					<span><?php esc_html_e( 'Show button "Application" on bottom screen', 'stionic-core' ); ?></span>
				</label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Auto open application', 'stionic-core' ); ?></th>
				<td class="has-checkbox">
				<label>
					<input type="checkbox" name="auto_open" <?php echo checked( @$data['auto_open'] ); ?> />
					<span><?php esc_html_e( 'Auto click button "Application"', 'stionic-core' ); ?></span>
				</label>
				</td>
			</tr>
		</tbody>
		</table>
		<button class="button button-primary button-large"><?php esc_html_e( 'Update' ); ?></button>
	</form>
</div>
