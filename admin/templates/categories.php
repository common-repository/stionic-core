<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
	$data = $this->option_categories; // Load data from database
?>
<div class="wrap stionic-categories">
	<h1><?php echo esc_html( __( 'Categories' ) . ' - ' . $this->display_name ); ?></h1>
	<noscript><div class="error message"><p><?php esc_html_e( 'Plugin require support Javascript to work', 'stionic-core' ); ?></p></div></noscript>
	<div class="update-success"><?php esc_html_e( 'Saved' ); ?></div>
	<div class="order-terms">
		<table class="form-table">
		<tbody>
			<tr>
				<th><?php esc_html_e( 'Application' ); ?></th>
				<td>
				<label>
					<input type="checkbox" name="show_all" <?php echo ( ! empty( $data['show_all'] ) ? 'checked' : null ); ?> />
					<span><?php esc_html_e( 'Show all categories in categories page (Uncheck: root categories only)', 'stionic-core' ); ?></span>
				</label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Sort categories' ); ?></th>
				<td>
				<label>
					<input type="checkbox" name="check_show" <?php echo ( ! empty( $data['check_show'] ) ? 'checked' : null ); ?> />
					<span><?php esc_html_e( 'Selected categories below will show in application (Uncheck: will hidden)', 'stionic-core' ); ?></span>
				</label>
				</td>
			</tr>
		</tbody>
		</table>
		<p class="description"><?php esc_html_e( 'Drag and drop then Update to sort the categories shown in the app.', 'stionic-core' ); ?></p>
		<div id="post-body">
			<ul class="sortable"><?php list_terms( 'category' ); ?></ul>
			<div class="clear"></div>
		</div>
		<div class="alignleft actions">
			<p class="submit"> <a href="javascript:;" class="save-order button-primary"><?php esc_html_e( 'Update' ); ?></a></p>
		</div>
	</div>
</div>
