<?php
/** Koji's homepage layout, with category navigation before the existing grid. */
get_header(); ?>

<main id="site-content" role="main">
	<div class="section-inner">
		<?php koji_d3_render_home_tabs(); ?>
		<div class="posts load-more-target" id="posts" aria-live="polite">
			<div class="grid-sizer"></div>
			<?php
			if ( have_posts() ) :
				while ( have_posts() ) :
					the_post();
					get_template_part( 'preview', get_post_type() );
				endwhile;
			endif;
			?>
		</div>
		<?php get_template_part( 'pagination' ); ?>
	</div>
</main>

<?php get_footer(); ?>
