<?php
$active = get_query_var( 'koji_d3_all' ) ? 'all' : ( is_post_type_archive( 'link' ) ? 'links' : 'posts' );
$posts_page = get_option( 'page_for_posts' );
$tabs = array(
	'posts' => array( __( 'Posts', 'koji-d3' ), $posts_page ? get_permalink( $posts_page ) : home_url( '/' ) ),
	'links' => array( __( 'Links', 'koji-d3' ), get_post_type_archive_link( 'link' ) ),
	'all' => array( __( 'All', 'koji-d3' ), get_option( 'permalink_structure' ) ? home_url( user_trailingslashit( '/all' ) ) : add_query_arg( 'koji_d3_all', '1', home_url( '/' ) ) ),
);
?>
<nav class="d3-feed-tabs" aria-label="<?php esc_attr_e( 'Content feeds', 'koji-d3' ); ?>">
	<?php foreach ( $tabs as $key => $tab ) : ?>
		<a href="<?php echo esc_url( $tab[1] ); ?>"<?php if ( $active === $key ) { echo ' aria-current="page"'; } ?>><?php echo esc_html( $tab[0] ); ?></a>
	<?php endforeach; ?>
</nav>
