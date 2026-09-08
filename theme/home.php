<?php get_header(); ?>
<main id="site-content" role="main">
	<div class="section-inner">
		<?php get_template_part( 'template-parts/feed-tabs' ); ?>
		<div class="posts load-more-target" id="posts" aria-live="polite">
			<div class="grid-sizer"></div>
			<?php
			if ( have_posts() ) {
				while ( have_posts() ) { the_post(); get_template_part( 'preview', get_post_type() ); }
			} else { ?><p><?php esc_html_e( 'No entries yet.', 'koji-d3' ); ?></p><?php }
			?>
		</div>
		<?php if ( get_query_var( 'koji_d3_all' ) || is_post_type_archive( 'link' ) ) {
			the_posts_pagination( array( 'mid_size' => 2 ) );
		} else { get_template_part( 'pagination' ); } ?>
	</div>
</main>
<?php get_footer(); ?>
