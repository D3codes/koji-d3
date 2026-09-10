<?php
/** Run in WordPress Playground after activating the child theme. */
require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
$manager = new WP_Customize_Manager();
$manager->add_section( 'koji_search_options', array( 'title' => 'Search' ) );
koji_d3_customize_color_scheme( $manager );
if ( 'Toggles' !== $manager->get_section( 'koji_search_options' )->title || 'koji_search_options' !== $manager->get_control( 'koji_d3_show_color_toggle' )->section ) {
	throw new Exception( 'Controls must share the renamed Toggles section.' );
}
$setting = $manager->get_setting( 'koji_d3_show_color_toggle' );
if ( false !== $setting->default || false !== $setting->sanitize( 'false' ) || true !== $setting->sanitize( '1' ) ) {
	throw new Exception( 'Unexpected Customizer default or checkbox sanitization.' );
}
foreach ( array( false, true ) as $enabled ) {
	set_theme_mod( 'koji_d3_show_color_toggle', $enabled );
	ob_start(); koji_d3_color_scheme_toggle(); $markup = ob_get_clean();
	ob_start(); koji_d3_color_scheme_head(); $head = ob_get_clean();
	if ( $enabled !== ( false !== strpos( $markup, 'role="switch"' ) ) || $enabled !== ( false !== strpos( $head, 'koji-d3-color-scheme' ) ) ) {
		throw new Exception( 'Visibility must gate both markup and early initialization.' );
	}
}
echo "Color scheme Customizer and rendering checks passed.\n";

$logo_control = $manager->get_control( 'koji_d3_dark_logo' );
if ( ! $logo_control instanceof WP_Customize_Media_Control || 'title_tagline' !== $logo_control->section || 'image' !== $logo_control->mime_type ) {
	throw new Exception( 'Dark logo must be an image upload in Site Identity.' );
}
$logo_ids = array();
foreach ( array( 'light', 'dark' ) as $scheme ) {
	$id = wp_insert_attachment( array( 'post_title' => $scheme, 'post_mime_type' => 'image/png', 'guid' => 'https://example.org/' . $scheme . '.png' ) );
	update_attached_file( $id, $scheme . '.png' );
	wp_update_attachment_metadata( $id, array( 'width' => 400, 'height' => 200, 'file' => $scheme . '.png' ) );
	$logo_ids[ $scheme ] = $id;
}
set_theme_mod( 'custom_logo', $logo_ids['light'] );
set_theme_mod( 'koji_d3_dark_logo', $logo_ids['dark'] );
set_theme_mod( 'koji_d3_show_color_toggle', true );
set_theme_mod( 'koji_retina_logo', true );
ob_start(); koji_d3_custom_logo(); $logos = ob_get_clean();
if ( 1 !== substr_count( $logos, '<a ' ) || 2 !== substr_count( $logos, '<img ' ) || 2 !== substr_count( $logos, 'width="200" height="100"' ) || false === strpos( $logos, 'd3-logo-dark' ) ) {
	throw new Exception( 'Both logo variants must share one home link and honor retina sizing.' );
}
foreach ( array( 0, 99999999 ) as $missing ) {
	set_theme_mod( 'koji_d3_dark_logo', $missing );
	ob_start(); koji_d3_custom_logo(); $logos = ob_get_clean();
	if ( 1 !== substr_count( $logos, '<img ' ) || false !== strpos( $logos, 'd3-dual-logo' ) ) {
		throw new Exception( 'Missing or deleted dark logos must fall back to the regular logo.' );
	}
}
set_theme_mod( 'koji_d3_dark_logo', $logo_ids['dark'] );
set_theme_mod( 'koji_d3_show_color_toggle', false );
ob_start(); koji_d3_custom_logo(); $logos = ob_get_clean();
if ( 1 !== substr_count( $logos, '<img ' ) || false !== strpos( $logos, 'd3-dual-logo' ) ) {
	throw new Exception( 'Disabling dark mode must retain the regular logo.' );
}
foreach ( $logo_ids as $id ) {
	wp_delete_attachment( $id, true );
}
remove_theme_mod( 'custom_logo' );
remove_theme_mod( 'koji_d3_dark_logo' );
remove_theme_mod( 'koji_retina_logo' );
echo "Dark logo upload, rendering, retina, and fallback checks passed.\n";

foreach ( array( false, true ) as $search ) {
	foreach ( array( false, true ) as $color ) {
		set_theme_mod( 'koji_disable_search', ! $search );
		set_theme_mod( 'koji_d3_show_color_toggle', $color );
		ob_start(); koji_d3_header_toggles(); $row = ob_get_clean();
		if ( $search !== ( false !== strpos( $row, 'class="toggle search-toggle"' ) ) || $color !== ( false !== strpos( $row, 'role="switch"' ) ) ) {
			throw new Exception( 'Unexpected control visibility.' );
		}
		if ( $search && $color && strpos( $row, 'class="toggle search-toggle"' ) > strpos( $row, 'role="switch"' ) ) {
			throw new Exception( 'Search must precede the appearance switch.' );
		}
	}
}

$header = file_get_contents( get_stylesheet_directory() . '/header.php' );
if ( strpos( $header, 'koji_d3_color_scheme_head();' ) > strpos( $header, 'wp_head();' ) ) {
	throw new Exception( 'Color scheme initialization must precede wp_head assets.' );
}
set_theme_mod( 'koji_d3_show_color_toggle', true );
ob_start(); koji_d3_color_scheme_head(); $critical = ob_get_clean();
if ( false === strpos( $critical, 'name="color-scheme"' ) || false === strpos( $critical, 'data-cfasync="false"' ) ) {
	throw new Exception( 'Early browser scheme hint and synchronous initialization are required.' );
}

$background = $manager->get_setting( 'koji_d3_dark_background_color' );
$control = $manager->get_control( 'koji_d3_dark_background_color' );
if ( '#171a20' !== $background->default || ! $control instanceof WP_Customize_Color_Control || 'colors' !== $control->section || null !== $background->sanitize( 'not-a-color' ) ) {
	throw new Exception( 'Dark background requires a validated Colors picker with the existing default.' );
}
set_theme_mod( 'koji_d3_show_color_toggle', true );
foreach ( array( '#123456' => '#123456', 'invalid' => '#171a20', '' => '#171a20' ) as $saved => $expected ) {
	set_theme_mod( 'koji_d3_dark_background_color', $saved );
	ob_start(); koji_d3_color_scheme_head(); $head = ob_get_clean();
	if ( false === strpos( $head, '--d3-dark-background:' . $expected . ';' ) ) {
		throw new Exception( 'Early canvas must use the saved dark background or safe default.' );
	}
}
remove_theme_mod( 'koji_d3_dark_background_color' );
echo "Dark background picker and early canvas checks passed.\n";
