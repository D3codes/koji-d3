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
