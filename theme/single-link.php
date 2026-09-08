<?php get_header(); ?>
<main id="site-content" role="main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'd3-single-link' ); ?> id="post-<?php the_ID(); ?>">
			<div class="post-inner">
				<h1 class="post-title"><?php the_title(); ?></h1>
				<?php get_template_part( 'template-parts/content-link' ); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
