<?php
/**
 * The template for displaying pages
 *
 * @package Interactivity_Theme
 */

get_header();
?>

<main id="primary" class="site-main single-page">

	<div class="container" data-theme-route-mount>
		<?php get_template_part( 'template-parts/route', 'page' ); ?>
	</div>

</main>

<?php
get_sidebar();
get_footer();
