<?php
/** Run in WordPress Playground after activating the child theme. */
require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
$manager = new WP_Customize_Manager();
koji_d3_customize_color_scheme( $manager );
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
