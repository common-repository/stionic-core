<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	// Update settings
if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'stionic-settings-nonce' ) && isset( $_POST['save'] ) ) {
	// Get data
	$data = array();
	if ( isset( $_POST['thumbnail_size_w'] ) && isset( $_POST['thumbnail_size_h'] ) ) {
		$data['stionic_thumbnail'] = array( absint( $_POST['thumbnail_size_w'] ), absint( $_POST['thumbnail_size_h'] ) );
	}
	if ( isset( $_POST['square_size_w'] ) && isset( $_POST['square_size_h'] ) ) {
		$data['stionic_square'] = array( absint( $_POST['square_size_w'] ), absint( $_POST['square_size_h'] ) );
	}
	if ( isset( $_POST['disable_featured'] ) ) {
		$data['disable_featured'] = (bool) $_POST['disable_featured'];
	}
	// Update
	if ( update_option( $this->media_name, array_filter( $data ) ) ) {
		echo '<div class="notice updated is-dismissible"><p>' . esc_html( __( 'Saved' ) ) . '</p></div>';
	}
} else {
	$data = $this->option_media; // Load data from database
}
?>
<div class="wrap stionic-media">
	<h1><?php echo esc_html( __( 'Media' ) . ' - ' . $this->display_name ); ?></h1>
	<form method="POST">
		<p class="description"><?php esc_html_e( 'The sizes listed below determine the maximum dimensions in pixels to use when adding an image to the Media Library.' ); ?></p>
		<input type="hidden" name="save" value="true" />
		<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'stionic-settings-nonce' ) ); ?>" />
		<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><?php esc_html_e( 'Thumbnail size' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Thumbnail size' ); ?></span></legend>
						<label for="thumbnail_size_w"><?php esc_html_e( 'Max Width' ); ?></label>
						<input name="thumbnail_size_w" type="number" step="1" min="0" id="thumbnail_size_w" value="<?php echo esc_attr( $data['stionic_thumbnail'][0] ); ?>" class="small-text">
						<label for="thumbnail_size_h"><?php esc_html_e( 'Max Height' ); ?></label>
						<input name="thumbnail_size_h" type="number" step="1" min="0" id="thumbnail_size_h" value="<?php echo esc_attr( $data['stionic_thumbnail'][1] ); ?>" class="small-text">
					</fieldset>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Thumbnail square size', 'stionic-core' ); ?></th>
				<td>
					<fieldset>
						<legend class="screen-reader-text"><span><?php esc_html_e( 'Thumbnail square size', 'stionic-core' ); ?></span></legend>
						<label for="square_size_w"><?php esc_html_e( 'Max Width' ); ?></label>
						<input name="square_size_w" type="number" step="1" min="0" id="square_size_w" value="<?php echo esc_attr( $data['stionic_square'][0] ); ?>" class="small-text">
						<label for="square_size_h"><?php esc_html_e( 'Max Height' ); ?></label>
						<input name="square_size_h" type="number" step="1" min="0" id="square_size_h" value="<?php echo esc_attr( $data['stionic_square'][1] ); ?>" class="small-text">
					</fieldset>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Default featured images', 'stionic-core' ); ?></th>
				<td>
				<label>
					<input type="checkbox" name="disable_featured" <?php checked( @$data['disable_featured'] ); ?> />
					<span><?php esc_html_e( 'Disable featured images in application detail page', 'stionic-core' ); ?></span>
				</label>
				</td>
			</tr>
		</tbody>
		</table>
		<button class="button button-primary button-large"><?php esc_html_e( 'Update' ); ?></button>
	</form>
</div>
