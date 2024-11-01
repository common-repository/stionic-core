<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
class Stionic_Admin_List_Posts {
	public $filterName  = 'stionic-list-posts';
	protected $postType = array( 'post', 'page' );
	protected $selections;

	public function __construct() {
		$this->selections = array(
			'rewards' => __( 'Rewards Ads', 'stionic-core' ),
		);
		// add filter
		add_filter( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
		add_filter( 'parse_query', array( $this, 'parse_query' ) );
	}
	function restrict_manage_posts() {
		global $typenow;
		if ( in_array( $typenow, $this->postType, true ) ) {
			$selected = isset( $_GET[ $this->filterName ] ) ? sanitize_text_field( $_GET[ $this->filterName ] ) : '';
			?>
			<select name="<?php echo esc_attr( $this->filterName ); ?>" id="stionic-list-posts">
				<option value="all" <?php selected( 'all', $selected ); ?>><?php esc_html_e( 'All', 'stionic-core' ); ?></option>
				<?php foreach ( $this->selections as $key => $name ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $selected ); ?>><?php echo esc_html( $name ); ?></option>
				<?php } ?>
			</select>
			<?php
		}
	}
	function parse_query( $query ) {
		global $pagenow;
		// Get the post type
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'post';
		// Get filter value
		$filterValue = isset( $_GET[ $this->filterName ] ) ? sanitize_text_field( $_GET[ $this->filterName ] ) : '';
		if ( is_admin() && $pagenow === 'edit.php' && in_array( $post_type, $this->postType, true ) && in_array( $filterValue, array_keys( $this->selections ), true ) ) {
			switch ( $filterValue ) {
				case 'rewards':
					$query->query_vars['meta_key']     = '_stionic_required_rewards_ads';
					$query->query_vars['meta_value']   = 0;
					$query->query_vars['meta_compare'] = '>';
					break;
				default:
					break;
			}
		}
	}

}
new Stionic_Admin_List_Posts();
