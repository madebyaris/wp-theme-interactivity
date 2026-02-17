<?php
/**
 * The template for displaying the footer
 *
 * @package Interactivity_Theme
 */

?>
	</div><!-- #content -->

	<footer id="colophon" class="site-footer">
		<div class="container">
			<?php
			// Footer widgets.
			if ( is_active_sidebar( 'footer-widgets' ) ) :
				?>
				<div class="footer-widgets">
					<?php dynamic_sidebar( 'footer-widgets' ); ?>
				</div>
			<?php endif; ?>

			<div class="site-info">
				<?php
				printf(
					'<p>&copy; %1$s %2$s. %3$s</p>',
					esc_html( gmdate( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) ),
					wp_kses(
						__( 'Powered by <a href="https://wordpress.org/">WordPress</a> and the Interactivity API', 'interactivity-theme' ),
						array(
							'a' => array(
								'href' => array(),
							),
						)
					)
				);
				?>

				<?php
				// Footer navigation.
				if ( has_nav_menu( 'footer' ) ) :
					wp_nav_menu(
						array(
							'theme_location'  => 'footer',
							'menu_id'         => 'footer-menu',
							'container'       => 'nav',
							'container_class' => 'footer-navigation',
							'fallback_cb'     => false,
						)
					);
				endif;
				?>
			</div>
		</div>
	</footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
