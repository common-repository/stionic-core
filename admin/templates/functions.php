<?php
function list_terms( $taxonomy ) {
	$args           = array(
		'orderby'    => 'term_group',
		'depth'      => 0,
		'child_of'   => 0,
		'hide_empty' => 0,
	);
	$taxonomy_terms = get_terms( $taxonomy, $args );
	$output         = '';
	if ( count( $taxonomy_terms ) > 0 ) {
		$output = to_walker_tree( $taxonomy_terms, $args['depth'], $args );
	}
	echo wp_kses(
		$output,
		array(
			'ul'    => array( 'class' => array() ),
			'li'    => array(
				'class' => array(),
				'id'    => array(),
			),
			'div'   => array( 'class' => array() ),
			'input' => array(
				'type'    => array(),
				'name'    => array(),
				'value'   => array(),
				'checked' => array(),
			),
			'span'  => array(),
		)
	);
}
function to_walker_tree( $taxonomy_terms, $depth, $r ) {
	$walker = new Stionic_Walker();
	$args   = array( $taxonomy_terms, $depth, $r );
	return call_user_func_array( array( &$walker, 'walk' ), $args );
}

