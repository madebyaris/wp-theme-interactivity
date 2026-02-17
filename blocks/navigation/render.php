<?php
/**
 * Navigation Block Render Template
 *
 * Variables available: $attributes, $content, $block
 *
 * @package Interactivity_Theme
 */

$menu_location = isset( $attributes['menuLocation'] ) ? $attributes['menuLocation'] : 'primary';
$menu_items    = array();

$locations = get_nav_menu_locations();
if ( isset( $locations[ $menu_location ] ) ) {
	$menu_obj = wp_get_nav_menu_object( $locations[ $menu_location ] );
	if ( $menu_obj ) {
		$menu_items = wp_get_nav_menu_items( $menu_obj->term_id );
	}
}

if ( ! $menu_items ) {
	$menu_items = array();
}

$aria_label = isset( $attributes['ariaLabel'] )
	? $attributes['ariaLabel']
	: __( 'Main Navigation', 'interactivity-theme' );
?>

<nav
	data-wp-interactive="interactivity-theme/navigation"
	class="wp-block-navigation"
	aria-label="<?php echo esc_attr( $aria_label ); ?>"
	data-wp-on--click="actions.navigate"
>
	<button
		class="wp-block-navigation__mobile-toggle"
		data-wp-on--click="actions.toggleMenu"
		data-wp-bind--aria-expanded="state.isMenuOpen"
		aria-label="<?php esc_attr_e( 'Toggle menu', 'interactivity-theme' ); ?>"
		aria-expanded="false"
	>
		<span class="wp-block-navigation__hamburger">
			<span></span>
			<span></span>
			<span></span>
		</span>
	</button>

	<ul
		class="wp-block-navigation__container"
		data-wp-class--is-open="state.isMenuOpen"
		data-wp-on--mouseenter="actions.prefetch"
	>
		<?php if ( ! empty( $menu_items ) ) : ?>
			<?php foreach ( $menu_items as $item ) : ?>
				<li class="wp-block-navigation__item">
					<a
						class="wp-block-navigation__link"
						href="<?php echo esc_url( $item->url ); ?>"
						<?php if ( $item->target ) : ?>
							target="<?php echo esc_attr( $item->target ); ?>"
						<?php endif; ?>
					>
						<?php echo esc_html( $item->title ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		<?php else : ?>
			<li class="wp-block-navigation__item">
				<a class="wp-block-navigation__link" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php esc_html_e( 'Home', 'interactivity-theme' ); ?>
				</a>
			</li>
		<?php endif; ?>
	</ul>
</nav>
