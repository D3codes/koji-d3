<?php
/** Run in WordPress Playground after activating Koji D3. */
$attributes = WP_Block_Type_Registry::get_instance()->get_registered( 'core/image' )->attributes;
if ( ! isset( $attributes['d3DarkImageId'] ) ) {
	throw new Exception( 'Image attribute is not registered.' );
}
$id = wp_insert_attachment( array( 'post_title' => 'Dark variant', 'post_mime_type' => 'image/png', 'guid' => 'https://example.org/dark.png' ) );
update_attached_file( $id, 'dark.png' );
wp_update_attachment_metadata( $id, array( 'width' => 400, 'height' => 200, 'file' => 'dark.png' ) );
$content = '<figure class="wp-block-image"><a href="/original"><img src="/light.png" srcset="/light.png 400w" sizes="400px" width="400" height="200" alt="Shared description" loading="lazy" /></a><figcaption>Caption</figcaption></figure>';
$block = array( 'attrs' => array( 'd3DarkImageId' => $id ) );
set_theme_mod( 'koji_d3_show_color_toggle', false );
if ( $content !== koji_d3_render_dark_image( $content, $block ) ) { throw new Exception( 'Disabled mode changed image.' ); }
set_theme_mod( 'koji_d3_show_color_toggle', true );
foreach ( array( 0, 999999 ) as $missing ) {
	if ( $content !== koji_d3_render_dark_image( $content, array( 'attrs' => array( 'd3DarkImageId' => $missing ) ) ) ) { throw new Exception( 'Missing image did not fall back.' ); }
}
$result = koji_d3_render_dark_image( $content, $block );
$html = new WP_HTML_Tag_Processor( $result );
$html->next_tag( 'IMG' );
$light = json_decode( $html->get_attribute( 'data-d3-image-light' ), true );
$dark = json_decode( $html->get_attribute( 'data-d3-image-dark' ), true );
if ( '/light.png 400w' !== $light['srcset'] || false === strpos( $dark['src'], 'dark.png' ) || 'Shared description' !== $html->get_attribute( 'alt' ) || '400' !== $html->get_attribute( 'width' ) ) { throw new Exception( 'Image metadata or presentation was lost.' ); }
if ( '/light.png' !== $html->get_attribute( 'src' ) || 'lazy' !== $html->get_attribute( 'loading' ) || false === strpos( $result, '<a href="/original">' ) || false === strpos( $result, '<figcaption>Caption</figcaption>' ) ) { throw new Exception( 'Original image, link or caption changed.' ); }
wp_delete_attachment( $id, true );
echo "Dark image registration, metadata, markup preservation and fallback checks passed.\n";
