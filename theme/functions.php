<?php

require_once get_stylesheet_directory() . '/inc/home-tabs.php';
require_once get_stylesheet_directory() . '/inc/customizer-home-tabs.php';

function koji_d3_enqueue_styles() {
    wp_enqueue_style(
        'koji-d3-style',
        get_stylesheet_uri(),
        array( 'koji-style' ),
        filemtime( get_stylesheet_directory() . '/style.css' )
    );
}
add_action( 'wp_enqueue_scripts', 'koji_d3_enqueue_styles', 20 );

/**
 * Prevent Koji's infinite-scroll check from running on the window load event.
 * The parent theme treats that event as a scroll, which can load page 2 before
 * the visitor has moved the page in a sufficiently tall viewport.
 */
function koji_d3_enqueue_scroll_pagination_fix() {
	wp_enqueue_script(
		'koji-d3-scroll-pagination',
		get_stylesheet_directory_uri() . '/assets/js/scroll-pagination.js',
		array( 'koji_construct' ),
		filemtime( get_stylesheet_directory() . '/assets/js/scroll-pagination.js' ),
		true
	);

	wp_localize_script(
		'koji-d3-scroll-pagination',
		'kojiD3Pagination',
		array(
			'preserveHomeUrl' => is_home() || is_front_page(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'koji_d3_enqueue_scroll_pagination_fix', 20 );
