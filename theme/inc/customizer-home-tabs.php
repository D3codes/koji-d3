<?php
/** Customizer-only repeater; stored as a sanitized array in a theme modification. */
function koji_d3_register_home_tabs_customizer( $wp_customize ) {
	class Koji_D3_Home_Tabs_Control extends WP_Customize_Control {
		public function enqueue() {
			wp_enqueue_style( 'koji-d3-tabs-editor', get_stylesheet_directory_uri() . '/assets/css/customizer-home-tabs.css', array(), filemtime( get_stylesheet_directory() . '/assets/css/customizer-home-tabs.css' ) );
			wp_enqueue_script( 'koji-d3-tabs-editor', get_stylesheet_directory_uri() . '/assets/js/customizer-home-tabs.js', array( 'customize-controls', 'wp-i18n' ), filemtime( get_stylesheet_directory() . '/assets/js/customizer-home-tabs.js' ), true );
			$categories = get_categories( array( 'hide_empty' => false ) );
			wp_localize_script( 'koji-d3-tabs-editor', 'kojiD3TabCategories', array_map( function( $category ) {
				return array( 'id' => $category->term_id, 'name' => $category->name );
			}, $categories ) );
		}

		public function render_content() {
			?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<p><?php esc_html_e( 'Check Default tab on the tab to show first when visiting the homepage. Only one tab can be the default. Add up to 20 tabs, then use Move up or Move down to set their order. Name changes preserve bookmarked URLs. Publish to save.', 'koji-d3' ); ?></p>
			<div class="home-tabs-editor"></div>
			<button type="button" class="button home-tabs-add"><?php esc_html_e( 'Add tab', 'koji-d3' ); ?></button>
			<p class="home-tabs-status screen-reader-text" aria-live="polite"></p>
			<?php
		}
	}

	$wp_customize->add_section( 'koji_d3_home_tabs_section', array( 'title' => __( 'Homepage Tabs', 'koji-d3' ), 'priority' => 120 ) );
	$wp_customize->add_setting( 'koji_d3_home_tabs_enabled', array( 'default' => true, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'koji_d3_home_tabs_enabled', array( 'section' => 'koji_d3_home_tabs_section', 'label' => __( 'Show homepage tab bar', 'koji-d3' ), 'type' => 'checkbox' ) );
	$wp_customize->add_setting( 'koji_d3_home_tabs', array( 'default' => array(), 'sanitize_callback' => 'koji_d3_sanitize_home_tabs' ) );
	$wp_customize->add_control( new Koji_D3_Home_Tabs_Control( $wp_customize, 'koji_d3_home_tabs', array( 'section' => 'koji_d3_home_tabs_section', 'label' => __( 'Tabs', 'koji-d3' ) ) ) );
}
add_action( 'customize_register', 'koji_d3_register_home_tabs_customizer' );
