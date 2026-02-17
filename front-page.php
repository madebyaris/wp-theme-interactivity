<?php
/**
 * Front page template
 * Used when the site front page is displayed (Settings > Reading).
 * Handles both "Your latest posts" and "A static page".
 *
 * @package Interactivity_Theme
 */

get_header();
?>

<main id="primary" class="site-main<?php echo is_home() ? '' : ' single-page'; ?>">

	<div class="container" data-theme-route-mount>
		<?php
		if ( is_home() ) {
			// "Your latest posts" as front page
			get_template_part( 'template-parts/route', 'loop' );
		} else {
			// "A static page" as front page
			get_template_part( 'template-parts/route', 'page' );
		}
		?>
	</div>

</main>

<?php
get_sidebar();
get_footer();
