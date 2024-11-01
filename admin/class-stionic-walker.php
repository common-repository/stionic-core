<?php
class Stionic_Walker extends Walker {
	public $db_fields      = array(
		'parent' => 'parent',
		'id'     => 'term_id',
	);
	protected $hide_in_app = array();
	public function __construct() {
		$this->hide_in_app = get_option( 'stionic-categories-hide', array() );
	}
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n$indent<ul class='children sortable'>\n";
	}
	function end_lvl( &$output, $depth = 0, $args = array() ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}
	function start_el( &$output, $term, $depth = 0, $args = array(), $current_object_id = 0 ) {
		if ( $depth ) {
			$indent = str_repeat( "\t", $depth );
		} else {
			$indent = '';
		}
		$taxonomy = get_taxonomy( $term->term_taxonomy_id );
		$output  .= $indent . '<li class="term_type_li" id="item_' . esc_attr( $term->term_id ) . '"><div class="item"><input type="checkbox" name="hide_in_app[]" value="' . esc_attr( $term->term_id ) . '" ' . ( in_array( strval( $term->term_id ), $this->hide_in_app, true ) ? 'checked="true"' : '' ) . '/><span>' . esc_html( apply_filters( 'to/term_title', $term->name, $term ) ) . ' </span></div>';
	}
	function end_el( &$output, $object, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}
}

