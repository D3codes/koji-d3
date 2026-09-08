<?php
/** Homepage and search preview for posts without a featured image. */
?>
<article <?php post_class( 'preview preview-' . get_post_type() . ' preview-text do-spot' ); ?> id="post-<?php the_ID(); ?>">
	<div class="preview-wrapper">
		<div class="preview-inner">
			<h2 class="preview-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<a class="preview-excerpt" href="<?php the_permalink(); ?>">
				<?php // Preserve excerpt formatting without nesting links or interactive elements.
				echo wp_kses( apply_filters( 'the_excerpt', get_the_excerpt() ), array( 'p' => array(), 'br' => array(), 'em' => array(), 'strong' => array() ) ); ?>
			</a>
			<?php koji_the_post_meta( get_the_ID(), 'preview' ); ?>
		</div>
	</div>
</article>
