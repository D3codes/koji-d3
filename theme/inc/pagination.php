<?php
/** Replace Koji's pluggable endpoint so mixed feeds keep their post types. */
function koji_d3_pagination_args( $raw_args ) {
	if ( ! is_array( $raw_args ) ) { return false; }
	$allowed_types = apply_filters( 'koji_allowed_post_types_for_lazy_loading', array( 'post', 'page', 'product', 'jetpack-portfolio', 'any', 'link' ) );
	$types = $raw_args['post_type'] ?? 'post';
	$types = $types ?: 'post';
	foreach ( (array) $types as $type ) {
		if ( ! is_string( $type ) || ! in_array( $type, $allowed_types, true ) || ( 'any' !== $type && ! is_post_type_viewable( $type ) ) ) { return false; }
	}
	$args = array_intersect_key( $raw_args, array_flip( array(
		'paged', 'posts_per_page', 'cat', 'category_name', 'tag', 'author', 's',
		'order', 'orderby', 'year', 'monthnum', 'day', 'ignore_sticky_posts',
	) ) );
	$args['post_type'] = $types;
	$args['post_status'] = 'publish';
	$args['paged'] = max( 1, absint( $args['paged'] ?? 1 ) );
	$args['posts_per_page'] = max( 1, min( absint( $args['posts_per_page'] ?? get_option( 'posts_per_page' ) ), max( 100, (int) get_option( 'posts_per_page' ) ) ) );
	return $args;
}

// Child functions load first; Koji deliberately makes this handler pluggable.
function koji_ajax_load_more() {
	check_ajax_referer( 'koji_ajax_load_more_nonce', 'nonce' );
	$raw = isset( $_POST['json_data'] ) && is_string( $_POST['json_data'] ) ? json_decode( wp_unslash( $_POST['json_data'] ), true ) : null;
	$args = koji_d3_pagination_args( $raw );
	if ( false === $args ) { wp_die( '', '', array( 'response' => 400 ) ); }
	$query = new WP_Query( $args );
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'preview', get_post_type() );
	}
	wp_reset_postdata();
	wp_die();
}
add_action( 'wp_ajax_nopriv_koji_ajax_load_more', 'koji_ajax_load_more' );
add_action( 'wp_ajax_koji_ajax_load_more', 'koji_ajax_load_more' );
