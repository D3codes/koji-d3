<?php
/** Run in disposable WordPress with Koji D3 activated. */
$id = wp_insert_post( array( 'post_title' => 'Search preview fixture', 'post_excerpt' => 'A recognizable excerpt.', 'post_status' => 'publish' ) );
remove_theme_mod( 'koji_fallback_image' );
set_theme_mod( 'koji_d3_home_tabs_enabled', false );
$query = new WP_Query( array( 's' => 'Search preview fixture' ) );
$GLOBALS['wp_query'] = $query;
$GLOBALS['wp_the_query'] = $query;
$_SERVER['REQUEST_URI'] = '/?s=Search+preview+fixture';
$query->the_post();
ob_start();
get_template_part( 'preview', get_post_type() );
$html = ob_get_clean();
if ( false === strpos( $html, 'A recognizable excerpt.' ) || false === strpos( $html, 'preview-text' ) || false !== strpos( $html, 'preview-image' ) ) {
	throw new RuntimeException( 'Search result must show text without a placeholder.' );
}
koji_d3_enqueue_home_tabs();
if ( ! wp_script_is( 'koji-d3-home-tabs', 'enqueued' ) || false === strpos( get_pagenum_link( 2, false ), 's=Search' ) ) {
	throw new RuntimeException( 'Search pagination must retain the search query and HTML loader.' );
}
$query->is_search = false;
ob_start();
get_template_part( 'preview', get_post_type() );
$html = ob_get_clean();
if ( false !== strpos( $html, 'preview-text' ) ) {
	throw new RuntimeException( 'Other preview contexts must retain the parent template.' );
}
echo "PASS: search text preview, search pagination, and other preview isolation\n";
wp_delete_post( $id, true );
