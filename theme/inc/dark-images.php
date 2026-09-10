<?php
/** Extend Image blocks without changing their saved HTML. */
function koji_d3_image_attributes( $args, $name ) {
	if ( 'core/image' === $name ) {
		$args['attributes']['d3DarkImageId'] = array( 'type' => 'number', 'default' => 0 );
	}
	return $args;
}
add_filter( 'register_block_type_args', 'koji_d3_image_attributes', 10, 2 );

function koji_d3_image_editor_assets() {
	wp_enqueue_script( 'koji-d3-image-editor', get_stylesheet_directory_uri() . '/assets/js/dark-image-editor.js', array( 'wp-hooks', 'wp-element', 'wp-compose', 'wp-block-editor', 'wp-components', 'wp-core-data', 'wp-data', 'wp-i18n' ), filemtime( get_stylesheet_directory() . '/assets/js/dark-image-editor.js' ), true );
}
add_action( 'enqueue_block_editor_assets', 'koji_d3_image_editor_assets' );

function koji_d3_dark_image_assets() {
	if ( get_theme_mod( 'koji_d3_show_color_toggle', false ) ) {
		wp_enqueue_script( 'koji-d3-dark-images', get_stylesheet_directory_uri() . '/assets/js/dark-images.js', array(), filemtime( get_stylesheet_directory() . '/assets/js/dark-images.js' ), false );
	}
}
add_action( 'wp_enqueue_scripts', 'koji_d3_dark_image_assets' );

function koji_d3_render_dark_image( $content, $block ) {
	$id = absint( $block['attrs']['d3DarkImageId'] ?? 0 );
	if ( ! $id || ! get_theme_mod( 'koji_d3_show_color_toggle', false ) || ! wp_attachment_is_image( $id ) ) {
		return $content;
	}
	$size = $block['attrs']['sizeSlug'] ?? 'full';
	$image = wp_get_attachment_image_src( $id, $size );
	if ( ! $image ) {
		return $content;
	}
	$html = new WP_HTML_Tag_Processor( $content );
	if ( $html->next_tag( 'IMG' ) ) {
		$light = array();
		foreach ( array( 'src', 'srcset', 'sizes' ) as $attribute ) {
			$light[ $attribute ] = $html->get_attribute( $attribute );
		}
		// Preserve the block's layout, alt text, caption, link and loading attributes.
		$dark = array( 'src' => $image[0], 'srcset' => wp_get_attachment_image_srcset( $id, $size ) ?: null, 'sizes' => wp_get_attachment_image_sizes( $id, $size ) ?: null );
		$html->set_attribute( 'data-d3-image-light', wp_json_encode( $light ) );
		$html->set_attribute( 'data-d3-image-dark', wp_json_encode( $dark ) );
	}
	return $html->get_updated_html();
}
add_filter( 'render_block_core/image', 'koji_d3_render_dark_image', 10, 2 );
