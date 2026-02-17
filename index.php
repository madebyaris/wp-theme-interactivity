<?php
/**
 * Main template file
 *
 * @package Interactivity_Theme
 */

get_header();
?>

<main id="primary" class="site-main">

	<div class="container" data-theme-route-mount>
		<?php get_template_part( 'template-parts/route', 'loop' ); ?>
	</div>

</main>

<?php
get_sidebar();
get_footer();
