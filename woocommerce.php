<?php
/**
 * WooCommerce template: shop, product archives, single product.
 * Cart and checkout use page.php (they are pages with shortcodes).
 *
 * @package Interactivity_Theme
 */

get_header();
?>

<?php woocommerce_content(); ?>

<?php
if ( is_active_sidebar( 'sidebar-shop' ) ) {
	get_sidebar( 'shop' );
} elseif ( is_active_sidebar( 'sidebar-1' ) ) {
	get_sidebar();
}
get_footer();
