<?php
/**
 * The template for displaying archive pages
 *
 * @package Interactivity_Theme
 */

get_header();
?>

<main id="primary" class="site-main archive-page">

	<div class="container" data-theme-route-mount>
		<?php get_template_part( 'template-parts/route', 'loop' ); ?>
	</div>

</main>

<?php
get_footer();
