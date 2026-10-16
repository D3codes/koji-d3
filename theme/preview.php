<?php
/** Match homepage text previews in search and single-post related previews. */
if ( ( is_search() || is_single() ) && ! has_post_thumbnail() ) {
	get_template_part( 'preview', 'text' );
} else {
	require get_template_directory() . '/preview.php';
}
