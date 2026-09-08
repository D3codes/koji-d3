<?php
/** Run with WordPress loaded in a disposable installation. */
function d3_check( $condition, $message ) {
	if ( ! $condition ) { echo 'FAIL: ' . $message . '\n'; throw new RuntimeException( $message ); }
	echo "PASS: $message\n";
}
function d3_main_query( $args ) {
	$GLOBALS['wp_the_query'] = new WP_Query();
	$GLOBALS['wp_query'] = $GLOBALS['wp_the_query'];
	$GLOBALS['wp_query']->query( $args );
	return $GLOBALS['wp_query'];
}
$fixture = function () {
	return array( 'response' => array( 'code' => 200 ), 'headers' => array(), 'body' => '<html><head><meta property="og:title" content="External title"><meta property="og:description" content="Short description"><meta property="og:site_name" content="Example"><title>Fallback</title></head></html>' );
};
add_filter( 'pre_http_request', $fixture );
$post = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => 'Normal post', 'post_date' => '2025-01-02 12:00:00' ) );
$link = wp_insert_post( array( 'post_type' => 'link', 'post_status' => 'publish', 'post_title' => 'Shared article', 'post_content' => 'My commentary.', 'post_date' => '2025-01-03 12:00:00' ) );
update_post_meta( $link, '_link_url', 'https://example.com/article' );
$preview = get_post_meta( $link, '_link_preview', true );
d3_check( 'External title' === $preview['title'], 'Open Graph metadata cached on URL save' );
remove_filter( 'pre_http_request', $fixture );
$failure = function () { return new WP_Error( 'offline' ); };
add_filter( 'pre_http_request', $failure );
koji_d3_fetch_preview( $link, 'https://example.com/article' );
d3_check( $preview === get_post_meta( $link, '_link_preview', true ), 'Cached preview survives failed refresh' );
d3_check( '' === koji_d3_link_url( 'javascript:alert(1)' ), 'Unsafe URL scheme rejected' );
$home = d3_main_query( array() );
d3_check( 'post' === $home->get( 'post_type' ), 'Homepage restricted to posts' );
$all = d3_main_query( array( 'koji_d3_all' => 1, 'posts_per_page' => 1 ) );
d3_check( array( 'post', 'link' ) === $all->get( 'post_type' ) && $all->get( 'ignore_sticky_posts' ) && 'date' === $all->get( 'orderby' ), 'All combines types by date without sticky promotion' );
$page1 = wp_list_pluck( $all->posts, 'ID' );
$page2 = d3_main_query( array( 'koji_d3_all' => 1, 'posts_per_page' => 1, 'paged' => 2 ) );
d3_check( ! array_intersect( $page1, wp_list_pluck( $page2->posts, 'ID' ) ) && $page2->max_num_pages >= 2, 'Combined pagination has distinct pages' );
$feed = d3_main_query( array( 'feed' => 'rss2' ) );
d3_check( array( 'post', 'link' ) === $feed->get( 'post_type' ), 'Primary RSS includes both types' );
$category = d3_main_query( array( 'feed' => 'rss2', 'cat' => 1 ) );
d3_check( array( 'post', 'link' ) !== $category->get( 'post_type' ), 'Category feed unchanged' );
$comments = d3_main_query( array( 'feed' => 'rss2', 'withcomments' => 1 ) );
d3_check( array( 'post', 'link' ) !== $comments->get( 'post_type' ), 'Comments feed unchanged' );
$search = d3_main_query( array( 's' => 'Shared article' ) );
d3_check( ! in_array( $link, wp_list_pluck( $search->posts, 'ID' ), true ), 'Links excluded from existing search results' );
$secondary = new WP_Query( array( 'posts_per_page' => 1 ) );
d3_check( array( 'post', 'link' ) !== $secondary->get( 'post_type' ), 'Secondary query unchanged' );
$GLOBALS['post'] = get_post( $link );
setup_postdata( $GLOBALS['post'] );
$rss = apply_filters( 'the_content_feed', 'My commentary.' );
d3_check( false !== strpos( $rss, 'My commentary.' ) && false !== strpos( $rss, 'https://example.com/article' ), 'RSS includes commentary and external destination' );
$summary = apply_filters( 'the_excerpt_rss', 'A manual excerpt.' );
d3_check( false !== strpos( $summary, 'My commentary.' ) && false !== strpos( $summary, 'https://example.com/article' ), 'Summary RSS preserves commentary and destination' );
update_post_meta( $link, '_link_url', 'https://example.com/different' );
d3_check( ! get_post_meta( $link, '_link_preview', true ), 'Changing destination clears mismatched cached metadata' );
remove_filter( 'pre_http_request', $failure );
wp_delete_post( $post, true );
wp_delete_post( $link, true );

$front = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Static home' ) );
$old_show = get_option( 'show_on_front' );
$old_front = get_option( 'page_on_front' );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $front );
$static_all = d3_main_query( array( 'koji_d3_all' => 1 ) );
d3_check( ! $static_all->is_page() && array( 'post', 'link' ) === $static_all->get( 'post_type' ) && ! $static_all->get( 'page_id' ), 'All works with a static homepage' );
update_option( 'show_on_front', $old_show );
update_option( 'page_on_front', $old_front );
wp_delete_post( $front, true );

$args = koji_d3_pagination_args( array( 'post_type' => array( 'post', 'link' ), 'paged' => 2, 'posts_per_page' => 2, 'ignore_sticky_posts' => true, 'post_status' => 'draft', 'orderby' => 'date', 'order' => 'DESC' ) );
d3_check( array( 'post', 'link' ) === $args['post_type'] && 2 === $args['paged'] && $args['ignore_sticky_posts'], 'AJAX retains mixed types, page and sticky policy' );
d3_check( 'publish' === $args['post_status'], 'AJAX cannot request drafts' );
d3_check( false === koji_d3_pagination_args( array( 'post_type' => array( 'post', 'revision' ) ) ), 'AJAX rejects disallowed post types' );
d3_check( 'link' === koji_d3_pagination_args( array( 'post_type' => 'link' ) )['post_type'], 'AJAX supports Link-only queries' );
