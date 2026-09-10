<?php
/** Optional visitor-controlled color scheme. */
function koji_d3_customize_color_scheme( $wp_customize ) {
	$section = $wp_customize->get_section( 'koji_search_options' );
	if ( $section ) {
		$section->title = __( 'Toggles', 'koji-d3' );
	}
	$wp_customize->add_setting( 'koji_d3_show_color_toggle', array(
		'default' => false,
		'sanitize_callback' => 'wp_validate_boolean',
	) );
	$wp_customize->add_control( 'koji_d3_show_color_toggle', array(
		'type' => 'checkbox',
		'section' => 'koji_search_options',
		'label' => __( 'Show light/dark mode toggle', 'koji-d3' ),
		'description' => __( 'Above the social icons in both menus. Uses the visitor’s system preference until they choose a mode. Hiding the toggle restores light mode.', 'koji-d3' ),
	) );
}
add_action( 'customize_register', 'koji_d3_customize_color_scheme', 20 );

/** Run before styles/body paint to avoid flashing the wrong saved scheme. */
function koji_d3_color_scheme_head() {
	if ( ! get_theme_mod( 'koji_d3_show_color_toggle', false ) ) {
		return;
	}
	// Paint the document canvas before external styles arrive (including navigation).
	echo '<style id="koji-d3-color-canvas">html[data-color-scheme="dark"]{color-scheme:dark;background:#171a20}html[data-color-scheme="dark"] body{background:#171a20}</style>';
	wp_print_inline_script_tag( file_get_contents( get_stylesheet_directory() . '/assets/js/color-scheme.js' ), array( 'id' => 'koji-d3-color-scheme' ) );
}
add_action( 'wp_head', 'koji_d3_color_scheme_head', 1 );

function koji_d3_color_scheme_toggle() {
	if ( ! get_theme_mod( 'koji_d3_show_color_toggle', false ) ) {
		return;
	}
	?>
	<button type="button" class="d3-color-toggle" role="switch" aria-checked="false" aria-label="<?php esc_attr_e( 'Dark mode', 'koji-d3' ); ?>" hidden>
		<span class="d3-color-toggle-option" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></svg></span>
		<span class="d3-color-toggle-option" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M20.5 14A9 9 0 0 1 10 3.5 9 9 0 1 0 20.5 14Z"/></svg></span>
	</button>
	<?php
}

/** Search and appearance controls share a left-aligned row above socials. */
function koji_d3_header_toggles() {
	$search = ! get_theme_mod( 'koji_disable_search', false );
	if ( ! $search && ! get_theme_mod( 'koji_d3_show_color_toggle', false ) ) {
		return;
	}
	?>
	<div class="d3-header-toggles">
		<?php if ( $search ) : ?>
			<div class="social-icons search-toggle-wrapper"><button type="button" aria-pressed="false" data-toggle-target=".search-overlay" data-set-focus=".search-overlay .search-field" class="toggle search-toggle"><span class="screen-reader-text"><?php esc_html_e( 'Toggle the search field', 'koji' ); ?></span></button></div>
		<?php endif; ?>
		<?php koji_d3_color_scheme_toggle(); ?>
	</div>
	<?php
}
