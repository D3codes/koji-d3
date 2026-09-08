<?php
/** Match homepage text previews in search while retaining Koji's other previews. */
if ( is_search() && ! has_post_thumbnail() ) {
	get_template_part( 'preview', 'text' );
} else {
	require get_template_directory() . '/preview.php';
}
