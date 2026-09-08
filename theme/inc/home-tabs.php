<?php
/** Homepage category filters. IDs are independent of editable labels. */
function koji_d3_sanitize_home_tabs( $value ) {
	if ( is_string( $value ) ) {
		$value = json_decode( $value, true );
	}
	$tabs = array();
	$seen = array();
	$has_default = false;
	foreach ( is_array( $value ) ? array_slice( $value, 0, 20 ) : array() as $tab ) {
		if ( ! is_array( $tab ) ) {
			continue;
		}
		$id = isset( $tab['id'] ) && is_string( $tab['id'] ) ? sanitize_key( $tab['id'] ) : '';
		$label = isset( $tab['label'] ) && is_string( $tab['label'] ) ? sanitize_text_field( $tab['label'] ) : '';
		$mode = isset( $tab['mode'] ) && in_array( $tab['mode'], array( 'include', 'all', 'exclude' ), true ) ? $tab['mode'] : '';
		if ( ! $id || ! $label || ! $mode || isset( $seen[ $id ] ) ) {
			continue;
		}
		$categories = array();
		foreach ( isset( $tab['categories'] ) && is_array( $tab['categories'] ) ? $tab['categories'] : array() as $category ) {
			if ( is_scalar( $category ) && ctype_digit( (string) $category ) && (int) $category > 0 ) {
				$categories[] = (int) $category;
			}
		}
		$is_default = ! $has_default && isset( $tab['default'] ) && true === $tab['default'];
		$has_default = $has_default || $is_default;
		$tabs[] = array( 'default' => $is_default, 'id' => $id, 'label' => $label, 'mode' => $mode, 'categories' => array_values( array_unique( $categories ) ) );
		$seen[ $id ] = true;
	}
	return $tabs;
}

function koji_d3_home_tabs() {
	return get_theme_mod( 'koji_d3_home_tabs_enabled', true )
		? koji_d3_sanitize_home_tabs( get_theme_mod( 'koji_d3_home_tabs', array() ) ) : array();
}

/** Old configurations and removed defaults safely fall back to the first tab. */
function koji_d3_default_home_tab( $tabs ) {
	foreach ( $tabs as $tab ) {
		if ( ! empty( $tab['default'] ) ) {
			return $tab;
		}
	}
	return $tabs ? $tabs[0] : null;
}

function koji_d3_active_home_tab() {
	$tabs = koji_d3_home_tabs();
	// This value only selects a saved definition; it never becomes a query argument.
	$id = isset( $_GET['tab'] ) && is_string( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
	foreach ( $tabs as $tab ) {
		if ( $tab['id'] === $id ) {
			return $tab;
		}
	}
	return koji_d3_default_home_tab( $tabs );
}

function koji_d3_is_home_tab_query( $query ) {
	return ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST )
		&& $query->is_main_query() && $query->is_home() && ! $query->is_feed()
		&& ! $query->is_search() && ! $query->is_archive();
}

function koji_d3_filter_home_tabs( $query ) {
	if ( ! koji_d3_is_home_tab_query( $query ) || ! ( $tab = koji_d3_active_home_tab() ) ) {
		return;
	}
	$query->set( 'post_type', 'post' );
	if ( 'all' === $tab['mode'] ) {
		return;
	}
	// Resolve all selected terms together, including empty categories; never per post.
	$ids = $tab['categories'] ? get_terms( array( 'taxonomy' => 'category', 'hide_empty' => false, 'include' => $tab['categories'], 'fields' => 'ids' ) ) : array();
	$ids = is_wp_error( $ids ) ? array() : array_map( 'intval', $ids );
	// Sticky promotion can otherwise insert posts outside an inclusion filter.
	$query->set( 'ignore_sticky_posts', true );
	if ( 'include' === $tab['mode'] ) {
		if ( $ids ) {
			$query->set( 'category__in', $ids );
		} else {
			$query->set( 'post__in', array( 0 ) );
		}
	} elseif ( $ids ) {
		$query->set( 'category__not_in', $ids );
	}
}
add_action( 'pre_get_posts', 'koji_d3_filter_home_tabs' );

function koji_d3_home_tabs_url() {
	$posts_page = (int) get_option( 'page_for_posts' );
	return 'page' === get_option( 'show_on_front' ) && $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
}

function koji_d3_render_home_tabs() {
	$active = koji_d3_active_home_tab();
	if ( ! $active ) {
		return;
	}
	$tabs = koji_d3_home_tabs();
	echo '<nav class="home-tabs" aria-label="' . esc_attr__( 'Post filters', 'koji-d3' ) . '">';
	$default = koji_d3_default_home_tab( $tabs );
	foreach ( $tabs as $tab ) {
		$url = $default['id'] === $tab['id'] ? koji_d3_home_tabs_url() : add_query_arg( 'tab', $tab['id'], koji_d3_home_tabs_url() );
		echo '<a href="' . esc_url( $url ) . '"' . ( $active['id'] === $tab['id'] ? ' aria-current="page"' : '' ) . '>' . esc_html( $tab['label'] ) . '</a>';
	}
	echo '</nav>';
}

function koji_d3_home_tab_page_link( $url ) {
	global $wp_query;
	if ( ! $wp_query || ! koji_d3_is_home_tab_query( $wp_query ) || ! ( $tab = koji_d3_active_home_tab() ) ) {
		return $url;
	}
	$tabs = koji_d3_home_tabs();
	$url = remove_query_arg( 'tab', $url );
	$default = koji_d3_default_home_tab( $tabs );
	return $default['id'] === $tab['id'] ? $url : add_query_arg( 'tab', $tab['id'], $url );
}
add_filter( 'get_pagenum_link', 'koji_d3_home_tab_page_link' );

function koji_d3_enqueue_home_tabs() {
	global $wp_query;
	if ( ! koji_d3_is_home_tab_query( $wp_query ) ) {
		return;
	}
	wp_enqueue_script( 'koji-d3-home-tabs', get_stylesheet_directory_uri() . '/assets/js/home-tabs.js', array( 'koji_construct' ), filemtime( get_stylesheet_directory() . '/assets/js/home-tabs.js' ), true );
	wp_localize_script( 'koji-d3-home-tabs', 'kojiD3HomeTabs', array( 'pageUrl' => get_pagenum_link( 987654321, false ) ) );
}
add_action( 'wp_enqueue_scripts', 'koji_d3_enqueue_home_tabs', 20 );
