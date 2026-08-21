<?php get_header(); ?>

<main id="site-content" role="main">

	<header class="single-container bg-color-white">

		<div class="post-inner section-inner">

			<h1><?php _e( 'Pay No Attention to the Man Behind the Curtain', 'koji' ); ?></h1>

			<img
				class="error-404-image"
				src="https://d3.codes/wp-content/uploads/2024/08/ezgif-3-62f03761b5.gif"
				alt="David typing furiously on a keyboard with a look of intense concentration on his face"
			>

			<p class="sans-excerpt"><?php _e( "Whatever you're looking for isn't here yet. It may have moved, it may never have existed, or (most likely) it's trapped in my head waiting to become a blog post.", 'koji' ); ?></p>

			<a class="go-home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Head home and check back soon', 'koji' ); ?> &rarr;</a>

		</div><!-- .post-inner -->

	</header><!-- .page-header -->

</main>

<?php get_footer(); ?>
