<?php
/**
 * The template for displaying single posts
 *
 * @package Interactivity_Theme
 */

get_header();
?>

<main id="primary" class="site-main single-post">

	<div class="container" data-theme-route-mount>
		<?php get_template_part( 'template-parts/route', 'single' ); ?>
	</div>

</main>

<?php
get_sidebar();
get_footer();
