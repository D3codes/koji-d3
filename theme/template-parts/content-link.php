<?php if ( post_password_required() ) { echo get_the_password_form(); return; } ?>
<?php if ( trim( get_the_content() ) ) : ?>
	<div class="d3-link-commentary entry-content"><?php the_content(); ?></div>
	<?php wp_link_pages(); ?>
<?php endif; ?>
<?php echo koji_d3_link_preview_html( get_the_ID() ); ?>
<p class="d3-link-permalink"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_date() ); ?> · <?php esc_html_e( 'Link & commentary', 'koji-d3' ); ?></a></p>
