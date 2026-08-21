			<footer id="site-footer" role="contentinfo">

				<?php if ( is_active_sidebar( 'footer-one' ) || is_active_sidebar( 'footer-two' ) || is_active_sidebar( 'footer-three' ) ) : ?>

					<div class="footer-widgets-outer-wrapper section-inner">

						<div class="footer-widgets-wrapper">

							<div class="footer-widgets">
								<?php dynamic_sidebar( 'footer-one' ); ?>
							</div>

							<div class="footer-widgets">
								<?php dynamic_sidebar( 'footer-two' ); ?>
							</div>

							<div class="footer-widgets">
								<?php dynamic_sidebar( 'footer-three' ); ?>
							</div>

						</div><!-- .footer-widgets-wrapper -->

					</div><!-- .footer-widgets-outer-wrapper.section-inner -->

				<?php endif; ?>

				<p class="credits">
					&copy;2024-<?php echo date( 'Y' ); ?> David Freeman
				</p><!-- .credits -->

			</footer><!-- #site-footer -->
			
			<?php wp_footer(); ?>

		</div><!-- #site-wrapper -->

	</body>
</html>
