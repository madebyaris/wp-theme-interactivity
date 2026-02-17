<?php
/**
 * The header for our theme
 *
 * @package Interactivity_Theme
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">

	<header id="masthead" class="site-header">
		<div class="container">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<div class="site-branding">
					<h1 class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<?php bloginfo( 'name' ); ?>
						</a>
					</h1>
					<?php
					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) :
						?>
						<p class="site-description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="header-actions">
				<nav id="site-navigation" class="main-navigation">

					<button
						class="menu-toggle"
						aria-controls="primary-menu"
						aria-expanded="false"
					>
						<span class="hamburger">
							<span></span>
							<span></span>
							<span></span>
						</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'interactivity-theme' ); ?></span>
					</button>

					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'menu_id'        => 'primary-menu',
							'fallback_cb'    => false,
							'container'      => false,
							'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
						)
					);
					?>
				</nav>

				<div class="header-search">
				<button
					class="search-toggle"
					aria-label="<?php esc_attr_e( 'Open search', 'interactivity-theme' ); ?>"
					aria-expanded="false"
					aria-controls="header-search-overlay"
				>
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="11" cy="11" r="8"></circle>
						<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
					</svg>
				</button>
				<div
					id="header-search-overlay"
					class="header-search-overlay"
					aria-hidden="true"
					role="dialog"
					aria-label="<?php esc_attr_e( 'Search', 'interactivity-theme' ); ?>"
				>
					<div class="header-search-overlay__backdrop" data-search-close></div>
					<div class="header-search-overlay__content">
						<?php echo do_blocks( '<!-- wp:interactivity-theme/search /-->' ); ?>
						<button
							type="button"
							class="header-search-overlay__close"
							aria-label="<?php esc_attr_e( 'Close search', 'interactivity-theme' ); ?>"
							data-search-close
						>
							&times;
						</button>
					</div>
				</div>
				</div>
			</div>
		</div>
	</header>

	<div id="content" class="site-content">
