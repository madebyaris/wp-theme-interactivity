<?php
/**
 * The template for displaying search results
 *
 * @package Interactivity_Theme
 */

get_header();
?>

<main id="primary" class="site-main search-results-page">

	<div class="container" data-theme-route-mount>
		<?php get_template_part( 'template-parts/route', 'loop' ); ?>
	</div>

</main>

<?php
get_footer();
