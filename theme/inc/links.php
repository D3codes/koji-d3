<?php
/** Link content, routing and cached previews. */
function koji_d3_link_url( $value ) {
	if ( ! is_string( $value ) ) { return ''; }
	$url = esc_url_raw( $value, array( 'http', 'https' ) );
	return in_array( wp_parse_url( $url, PHP_URL_SCHEME ), array( 'http', 'https' ), true ) && wp_parse_url( $url, PHP_URL_HOST ) ? $url : '';
}

function koji_d3_register_links() {
	register_post_type( 'link', array(
		'labels' => array( 'name' => __( 'Links', 'koji-d3' ), 'singular_name' => __( 'Link', 'koji-d3' ), 'add_new' => __( 'Add New Link', 'koji-d3' ), 'add_new_item' => __( 'Add New Link', 'koji-d3' ), 'edit_item' => __( 'Edit Link', 'koji-d3' ), 'all_items' => __( 'All Links', 'koji-d3' ) ),
		'public' => true, 'show_in_rest' => true, 'exclude_from_search' => true,
		'has_archive' => 'links', 'rewrite' => array( 'slug' => 'links', 'with_front' => false ),
		'menu_icon' => 'dashicons-admin-links',
		'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'revisions' ),
	) );
	register_post_meta( 'link', '_link_url', array(
		'type' => 'string', 'single' => true, 'show_in_rest' => true,
		'sanitize_callback' => 'koji_d3_link_url',
		'auth_callback' => function ( $allowed, $key, $id ) { return current_user_can( 'edit_post', $id ); },
	) );
	add_rewrite_rule( '^all/page/([0-9]+)/?$', 'index.php?koji_d3_all=1&paged=$matches[1]', 'top' );
	add_rewrite_rule( '^all/?$', 'index.php?koji_d3_all=1', 'top' );
}
add_action( 'init', 'koji_d3_register_links' );
add_filter( 'query_vars', function ( $vars ) { $vars[] = 'koji_d3_all'; return $vars; } );

function koji_d3_feed_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) { return; }
	// Only the unfiltered primary posts feed; preserve every specialized feed.
	if ( $query->is_feed() ) {
		if ( ! $query->is_comment_feed && ! $query->is_archive() && ! $query->is_singular() && ! $query->is_search() && ! $query->get( 'post_type' ) && ! $query->get( 'koji_d3_all' ) ) {
			$query->set( 'post_type', array( 'post', 'link' ) );
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}
		return;
	}
	if ( $query->get( 'koji_d3_all' ) ) {
		$query->is_home = true;
		$query->is_page = false;
		$query->is_singular = false;
		$query->set( 'page_id', 0 );
		$query->set( 'post_type', array( 'post', 'link' ) );
		$query->set( 'ignore_sticky_posts', true );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	} elseif ( $query->is_home() ) {
		$query->set( 'post_type', 'post' );
	} elseif ( $query->is_post_type_archive( 'link' ) ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', 'koji_d3_feed_query' );
add_filter( 'template_include', function ( $template ) {
	return get_query_var( 'koji_d3_all' ) && ! is_feed() ? locate_template( 'home.php' ) : $template;
} );
add_filter( 'redirect_canonical', function ( $redirect ) { return get_query_var( 'koji_d3_all' ) ? false : $redirect; } );

add_action( 'add_meta_boxes_link', function () {
	add_meta_box( 'koji-d3-link-url', __( 'External URL', 'koji-d3' ), function ( $post ) {
		wp_nonce_field( 'koji_d3_save_link', 'koji_d3_link_nonce' );
		?><p><label for="koji-d3-url"><?php esc_html_e( 'Full HTTP or HTTPS URL', 'koji-d3' ); ?></label></p>
		<input type="url" class="widefat" id="koji-d3-url" name="koji_d3_link_url" value="<?php echo esc_attr( get_post_meta( $post->ID, '_link_url', true ) ); ?>" placeholder="https://example.com/article">
		<p><?php esc_html_e( 'Preview information is fetched when this URL changes. A featured image overrides the remote image.', 'koji-d3' ); ?></p>
		<p><label><input type="checkbox" name="koji_d3_refresh_preview" value="1"> <?php esc_html_e( 'Refresh cached preview on save', 'koji-d3' ); ?></label></p><?php
	}, 'link', 'normal', 'high' );
} );
add_action( 'save_post_link', function ( $id ) {
	if ( wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) || ! isset( $_POST['koji_d3_link_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['koji_d3_link_nonce'] ) ), 'koji_d3_save_link' ) || ! current_user_can( 'edit_post', $id ) ) { return; }
	if ( isset( $_POST['koji_d3_link_url'] ) ) {
		$url = koji_d3_link_url( wp_unslash( $_POST['koji_d3_link_url'] ) );
		$old = get_post_meta( $id, '_link_url', true );
		update_post_meta( $id, '_link_url', $url );
		if ( $url === $old && ! empty( $_POST['koji_d3_refresh_preview'] ) ) { koji_d3_fetch_preview( $id, $url ); }
	}
} );

// Metadata hooks cover both the normal editor and REST updates.
function koji_d3_link_meta_changed( $meta_id, $id, $key, $value ) {
	if ( '_link_url' === $key && 'link' === get_post_type( $id ) ) { koji_d3_fetch_preview( $id, $value ); }
}
add_action( 'added_post_meta', 'koji_d3_link_meta_changed', 10, 4 );
add_action( 'updated_post_meta', 'koji_d3_link_meta_changed', 10, 4 );

function koji_d3_fetch_preview( $id, $url ) {
	$cached = get_post_meta( $id, '_link_preview', true );
	// Never show metadata belonging to a different destination.
	if ( ! is_array( $cached ) || ( $cached['url'] ?? '' ) !== $url ) { delete_post_meta( $id, '_link_preview' ); }
	if ( ! $url ) { return; }
	// WordPress validates the destination and redirects against private-network URLs.
	$response = wp_safe_remote_get( $url, array( 'timeout' => 5, 'redirection' => 3, 'limit_response_size' => 524288, 'headers' => array( 'Accept' => 'text/html' ) ) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) || ! class_exists( 'DOMDocument' ) ) { return; }
	$html = wp_remote_retrieve_body( $response );
	$doc = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded = $doc->loadHTML( '<?xml encoding="UTF-8">' . $html, LIBXML_NONET );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );
	if ( ! $loaded ) { return; }
	$meta = array();
	foreach ( $doc->getElementsByTagName( 'meta' ) as $tag ) {
		$key = strtolower( $tag->getAttribute( 'property' ) ?: $tag->getAttribute( 'name' ) );
		$meta[ $key ] = $tag->getAttribute( 'content' );
	}
	$title = $doc->getElementsByTagName( 'title' )->item( 0 );
	$preview = array(
		'url' => $url,
		'title' => sanitize_text_field( $meta['og:title'] ?? ( $title ? $title->textContent : '' ) ),
		'site' => sanitize_text_field( $meta['og:site_name'] ?? '' ),
		'description' => wp_trim_words( sanitize_text_field( $meta['og:description'] ?? $meta['description'] ?? '' ), 55 ),
		'image' => '',
	);
	if ( ! empty( $meta['og:image'] ) ) {
		$image = koji_d3_link_url( WP_Http::make_absolute_url( $meta['og:image'], $url ) );
		$preview['image'] = wp_http_validate_url( $image ) ? $image : '';
	}
	update_post_meta( $id, '_link_preview', $preview );
}

function koji_d3_link_preview_html( $id ) {
	$url = get_post_meta( $id, '_link_url', true );
	if ( ! $url || post_password_required( $id ) ) { return ''; }
	$preview = get_post_meta( $id, '_link_preview', true );
	$preview = is_array( $preview ) && ( $preview['url'] ?? '' ) === $url ? $preview : array();
	$title = get_the_title( $id ) ?: ( $preview['title'] ?? $url );
	ob_start(); ?>
	<a class="d3-link-preview" href="<?php echo esc_url( $url ); ?>">
		<?php if ( has_post_thumbnail( $id ) ) { echo get_the_post_thumbnail( $id, 'large' ); }
		elseif ( ! empty( $preview['image'] ) ) { ?><img src="<?php echo esc_url( $preview['image'] ); ?>" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer"><?php } ?>
		<span class="d3-link-preview-text">
			<strong class="d3-link-title"><?php echo esc_html( $title ); ?></strong>
			<span class="d3-link-source"><?php echo esc_html( wp_parse_url( $url, PHP_URL_HOST ) ); ?><?php if ( ! empty( $preview['site'] ) ) { echo ' · ' . esc_html( $preview['site'] ); } ?></span>
			<?php if ( ! empty( $preview['description'] ) ) { ?><span class="d3-link-description"><?php echo esc_html( $preview['description'] ); ?></span><?php } ?>
		</span>
	</a>
	<?php return ob_get_clean();
}
function koji_d3_link_feed_content( $content ) {
	return 'link' === get_post_type() ? $content . koji_d3_link_preview_html( get_the_ID() ) : $content;
}
add_filter( 'the_content_feed', 'koji_d3_link_feed_content' );
add_filter( 'the_excerpt_rss', function ( $excerpt ) {
	if ( 'link' !== get_post_type() || post_password_required() ) { return $excerpt; }
	return koji_d3_link_feed_content( apply_filters( 'the_content', get_the_content() ) );
} );
