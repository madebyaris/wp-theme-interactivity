<?php
/**
 * Shop sidebar for WooCommerce pages
 *
 * @package Interactivity_Theme
 */

if ( ! is_active_sidebar( 'sidebar-shop' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area sidebar sidebar-shop">
	<?php dynamic_sidebar( 'sidebar-shop' ); ?>
</aside>
