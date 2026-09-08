<?php
/** Homepage preview for posts without a featured image. */
?>
<article <?php post_class( 'preview preview-' . get_post_type() . ' preview-text do-spot' ); ?> id="post-<?php the_ID(); ?>">
	<div class="preview-wrapper">
		<div class="preview-inner">
			<h2 class="preview-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			<div class="preview-excerpt">
				<?php the_excerpt(); ?>
			</div>
			<?php koji_the_post_meta( get_the_ID(), 'preview' ); ?>
		</div>
	</div>
</article>
