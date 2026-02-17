<?php
/**
 * Interactivity Theme Functions
 *
 * @package Interactivity_Theme
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup
 */
function interactivity_theme_setup() {
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'interactivity-theme' ),
			'footer'  => __( 'Footer Menu', 'interactivity-theme' ),
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support( 'customize-selective-refresh-widgets' );
}
add_action( 'after_setup_theme', 'interactivity_theme_setup' );

/**
 * Set the content width in pixels
 */
function interactivity_theme_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'interactivity_theme_content_width', 800 );
}
add_action( 'after_setup_theme', 'interactivity_theme_content_width', 0 );

/**
 * Register widget areas
 */
function interactivity_theme_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Sidebar', 'interactivity-theme' ),
			'id'            => 'sidebar-1',
			'description'   => __( 'Add widgets here to appear in your sidebar.', 'interactivity-theme' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( 'Footer Widgets', 'interactivity-theme' ),
			'id'            => 'footer-widgets',
			'description'   => __( 'Add widgets here to appear in your footer.', 'interactivity-theme' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'interactivity_theme_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function interactivity_theme_scripts() {
	wp_enqueue_style(
		'interactivity-theme-style',
		get_stylesheet_uri(),
		array(),
		'1.0.0'
	);

	// The header navigation uses Interactivity API directives outside block content.
	// Enqueue the compiled navigation runtime explicitly so actions are available.
	$navigation_asset_file  = get_template_directory() . '/build/blocks/navigation/view.asset.php';
	$navigation_script_file = get_template_directory() . '/build/blocks/navigation/view.js';

	if ( file_exists( $navigation_asset_file ) && file_exists( $navigation_script_file ) ) {
		$navigation_asset   = include $navigation_asset_file;
		$navigation_version = isset( $navigation_asset['version'] ) ? $navigation_asset['version'] : '1.0.0';
		// Load in head with defer so it runs before footer scripts (WooCommerce etc) - critical for SPA.
		wp_enqueue_script(
			'interactivity-theme-navigation-view',
			get_template_directory_uri() . '/build/blocks/navigation/view.js',
			array(),
			$navigation_version,
			false
		);
		wp_localize_script(
			'interactivity-theme-navigation-view',
			'interactivityTheme',
			array(
				'apiBase'  => rest_url( 'wp/v2' ),
				'homeUrl'  => home_url( '/' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'interactivity_theme_scripts', 5 );

/**
 * Add defer to navigation script so it runs early without blocking render.
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @param string $src    The script source URL.
 */
function interactivity_theme_script_loader_tag( $tag, $handle, $src ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by filter signature.
	if ( 'interactivity-theme-navigation-view' === $handle ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'interactivity_theme_script_loader_tag', 10, 3 );

/**
 * Inline script in head: intercept internal link clicks BEFORE any plugin scripts.
 * Prevents default and dispatches event; main view.js handles the fetch.
 */
function interactivity_theme_spa_intercept_script() {
	$script = "
	(function() {
		document.addEventListener('click', function(e) {
			var a = e.target && e.target.closest ? e.target.closest('a') : null;
			if (!a || !a.href) return;
			if (a.target === '_blank' || a.getAttribute('href').charAt(0) === '#' || a.hasAttribute('download')) return;
			if (a.closest('[data-no-csr]')) return;
			if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
			try {
				var url = new URL(a.href);
				if (url.origin !== location.origin || url.pathname.indexOf('/wp-admin') === 0 || url.pathname.indexOf('/wp-login') === 0) return;
			} catch (err) { return; }
			e.preventDefault();
			e.stopImmediatePropagation();
			window.dispatchEvent(new CustomEvent('theme:spa:navigate', { detail: { href: a.href } }));
		}, true);
	})();
	";
	wp_print_inline_script_tag( $script, array( 'id' => 'interactivity-theme-spa-intercept' ) );
}
add_action( 'wp_head', 'interactivity_theme_spa_intercept_script', 1 );

/**
 * WooCommerce compatibility: prevent order-attribution script from loading on non-checkout pages.
 * Fixes "CustomElementRegistry: wc-order-attribution-inputs already used" error that occurs
 * when the script runs twice (e.g. with SPA navigation or duplicate loading).
 * Order attribution still works on checkout/cart where it's needed.
 */
function interactivity_theme_woocommerce_order_attribution_fix() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	if ( ! is_checkout() && ! is_cart() ) {
		wp_dequeue_script( 'wc-order-attribution' );
	}
}
add_action( 'wp_print_scripts', 'interactivity_theme_woocommerce_order_attribution_fix', 100 );

/**
 * Register custom blocks from the build directory.
 *
 * Each block folder in build/blocks/ must contain a block.json that
 * references the compiled view.js, index.js, render.php, and style.css.
 */
function interactivity_theme_register_blocks() {
	$blocks = array( 'navigation', 'search', 'counter', 'accordion' );

	foreach ( $blocks as $block ) {
		$block_dir = get_template_directory() . '/build/blocks/' . $block;

		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}
}
add_action( 'init', 'interactivity_theme_register_blocks' );

/**
 * Get navigation menu items as a structured array.
 *
 * @param string $location Menu location slug. Default 'primary'.
 * @return array Menu items.
 */
function interactivity_theme_get_nav_menu_items( $location = 'primary' ) {
	$locations = get_nav_menu_locations();

	if ( ! isset( $locations[ $location ] ) ) {
		return array();
	}

	$menu = wp_get_nav_menu_object( $locations[ $location ] );

	if ( ! $menu ) {
		return array();
	}

	$menu_items = wp_get_nav_menu_items( $menu->term_id );

	return $menu_items ? $menu_items : array();
}

/**
 * Custom REST API endpoint with server-side caching.
 */
function interactivity_theme_register_cached_routes() {
	register_rest_route(
		'wp/v2',
		'/cached/posts',
		array(
			'methods'             => 'GET',
			'callback'            => 'interactivity_theme_cached_posts',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'wp/v2',
		'/cached/pages',
		array(
			'methods'             => 'GET',
			'callback'            => 'interactivity_theme_cached_pages',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'interactivity_theme_register_cached_routes' );

/**
 * Cached posts endpoint.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|array Response.
 */
function interactivity_theme_cached_posts( $request ) {
	$params    = $request->get_query_params();
	$cache_key = 'theme_cached_posts_' . md5( wp_json_encode( $params ) );

	$cached = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	// Build query args.
	$args = array(
		'posts_per_page' => isset( $params['per_page'] ) ? intval( $params['per_page'] ) : 10,
		'paged'          => isset( $params['page'] ) ? intval( $params['page'] ) : 1,
		'post_status'    => 'publish',
	);

	if ( ! empty( $params['search'] ) ) {
		$args['s'] = sanitize_text_field( $params['search'] );
	}

	if ( ! empty( $params['categories'] ) ) {
		$args['cat'] = intval( $params['categories'] );
	}

	if ( ! empty( $params['tags'] ) ) {
		$args['tag_id'] = intval( $params['tags'] );
	}

	$query = new WP_Query( $args );
	$posts = array();

	foreach ( $query->posts as $post ) {
		$posts[] = array(
			'id'      => $post->ID,
			'title'   => array( 'rendered' => get_the_title( $post->ID ) ),
			'content' => array( 'rendered' => apply_filters( 'the_content', $post->post_content ) ),
			'excerpt' => array( 'rendered' => apply_filters( 'the_excerpt', get_post( $post->ID )->post_excerpt ) ),
			'link'    => get_permalink( $post->ID ),
			'date'    => $post->post_date,
		);
	}

	$response = rest_ensure_response( $posts );

	// Cache for 5 minutes.
	set_transient( $cache_key, $response, 300 );

	return $response;
}

/**
 * Cached pages endpoint.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|array Response.
 */
function interactivity_theme_cached_pages( $request ) {
	$params    = $request->get_query_params();
	$cache_key = 'theme_cached_pages_' . md5( wp_json_encode( $params ) );

	$cached = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$args = array(
		'post_type'      => 'page',
		'posts_per_page' => isset( $params['per_page'] ) ? intval( $params['per_page'] ) : 10,
		'paged'          => isset( $params['page'] ) ? intval( $params['page'] ) : 1,
		'post_status'    => 'publish',
	);

	if ( ! empty( $params['slug'] ) ) {
		$args['pagename'] = sanitize_text_field( $params['slug'] );
	}

	$query = new WP_Query( $args );
	$pages = array();

	foreach ( $query->posts as $post ) {
		$pages[] = array(
			'id'      => $post->ID,
			'title'   => array( 'rendered' => get_the_title( $post->ID ) ),
			'content' => array( 'rendered' => apply_filters( 'the_content', $post->post_content ) ),
			'link'    => get_permalink( $post->ID ),
		);
	}

	$response = rest_ensure_response( $pages );

	// Cache for 5 minutes.
	set_transient( $cache_key, $response, 300 );

	return $response;
}

/**
 * Clear cache when posts are updated.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object. Unused but required by hook signature.
 * @param bool    $update  Whether this is an update. Unused but required by hook signature.
 */
function interactivity_theme_clear_post_cache( $post_id, $post, $update ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by save_post hook.
	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	// Clear all cached posts.
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk transient purge.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_theme_cached_posts_' ) . '%'
		)
	);
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bulk transient purge.
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
			$wpdb->esc_like( '_transient_theme_cached_pages_' ) . '%'
		)
	);
}
add_action( 'save_post', 'interactivity_theme_clear_post_cache', 10, 3 );

/**
 * REST field: body_classes for SPA navigation.
 * Returns body class list for post/page so client can update document.body on route change.
 */
function interactivity_theme_register_rest_fields() {
	register_rest_field(
		array( 'post', 'page' ),
		'body_classes',
		array(
			'get_callback' => function ( $obj ) {
				$post = get_post( $obj['id'] );
				if ( ! $post ) {
					return array();
				}
				$post_classes = get_post_class( '', $post );
				$base = array( 'single', 'single-' . $post->post_type, 'postid-' . $post->ID );
				return array_unique( array_merge( $base, $post_classes ) );
			},
			'schema'       => array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			),
		)
	);
}
add_action( 'rest_api_init', 'interactivity_theme_register_rest_fields', 20 );

/**
 * REST endpoint: global styles (theme.json + customizer CSS) for SPA.
 */
function interactivity_theme_register_theme_endpoints() {
	register_rest_route(
		'wp/v2',
		'/theme/global-styles',
		array(
			'methods'             => 'GET',
			'callback'            => 'interactivity_theme_global_styles',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'interactivity_theme_register_theme_endpoints' );

/**
 * Return global stylesheet and custom CSS for client injection.
 */
function interactivity_theme_global_styles() {
	$stylesheet = '';
	if ( function_exists( 'wp_get_global_stylesheet' ) ) {
		$stylesheet .= wp_get_global_stylesheet();
	}
	if ( function_exists( 'wp_get_custom_css' ) ) {
		$custom = wp_get_custom_css();
		if ( ! empty( $custom ) ) {
			$stylesheet .= "\n/* Customizer additional CSS */\n" . $custom;
		}
	}
	return rest_ensure_response(
		array(
			'css' => $stylesheet,
		)
	);
}

/**
 * REST endpoint: required assets (CSS/JS) for a post/page.
 * Used by SPA to dynamically load block/plugin assets on route change.
 */
function interactivity_theme_register_post_assets_route() {
	register_rest_route(
		'wp/v2',
		'/theme/post-assets',
		array(
			'methods'             => 'GET',
			'callback'            => 'interactivity_theme_post_assets',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'interactivity_theme_register_post_assets_route' );

/**
 * REST endpoint: fetch page by full path (for hierarchical pages).
 */
function interactivity_theme_register_page_by_path_route() {
	register_rest_route(
		'wp/v2',
		'/theme/page-by-path',
		array(
			'methods'             => 'GET',
			'callback'            => 'interactivity_theme_page_by_path',
			'permission_callback' => '__return_true',
			'args'                => array(
				'path' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'interactivity_theme_register_page_by_path_route' );

/**
 * REST endpoint: server-rendered HTML for SPA routes.
 * Returns theme/Gutenberg output - no client-side template building.
 */
function interactivity_theme_register_route_html_route() {
	register_rest_route(
		'wp/v2',
		'/theme/route-html',
		array(
			'methods'             => 'GET',
			'callback'            => 'interactivity_theme_route_html',
			'permission_callback' => '__return_true',
			'args'                => array(
				'path' => array(
					'required'          => true,
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'interactivity_theme_register_route_html_route' );

/**
 * Return server-rendered HTML for a route (theme/Gutenberg design).
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response Response with html, title, bodyClasses, postId.
 */
function interactivity_theme_route_html( $request ) {
	$path   = trim( $request->get_param( 'path' ), '/' );
	$page   = max( 1, (int) $request->get_param( 'page' ) );
	$search = $request->get_param( 's' );

	global $wp_query, $wp_the_query;

	$original_query     = $wp_query;
	$original_the_query = $wp_the_query;

	/*
	 * Temporarily override $wp_query / $wp_the_query for template rendering.
	 * Restored before return.
	 */
	// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited

	$query_args = array(
		'post_status'    => 'publish',
		'posts_per_page' => get_option( 'posts_per_page', 10 ),
		'paged'          => $page,
	);

	if ( ! empty( $search ) ) {
		$query_args['s'] = $search;
		$wp_query        = new WP_Query( $query_args );
		$wp_the_query    = $wp_query;
	} elseif ( empty( $path ) || preg_match( '#^page/(\d+)$#', $path, $m ) ) {
		// Home (optionally page/2, page/3...).
		if ( preg_match( '#^page/(\d+)$#', $path, $m ) ) {
			$query_args['paged'] = (int) $m[1];
		}
		$wp_query     = new WP_Query( $query_args );
		$wp_the_query = $wp_query;
	} else {
		$path_parts = explode( '/', $path );
		$first      = $path_parts[0] ?? '';

		if ( 'category' === $first && isset( $path_parts[1] ) ) {
			$term = get_term_by( 'slug', $path_parts[1], 'category' );
			if ( $term ) {
				$query_args['cat'] = $term->term_id;
			}
			$wp_query     = new WP_Query( $query_args );
			$wp_the_query = $wp_query;
		} elseif ( 'tag' === $first && isset( $path_parts[1] ) ) {
			$term = get_term_by( 'slug', $path_parts[1], 'post_tag' );
			if ( $term ) {
				$query_args['tag_id'] = $term->term_id;
			}
			$wp_query     = new WP_Query( $query_args );
			$wp_the_query = $wp_query;
		} else {
			// Single post or page (path can be "slug" or "parent/child").
			$page_obj = get_page_by_path( $path, OBJECT, 'page' );
			if ( $page_obj && 'publish' === $page_obj->post_status ) {
				$wp_query     = new WP_Query( array( 'page_id' => $page_obj->ID ) );
				$wp_the_query = $wp_query;
			} else {
				$slug  = $path_parts[ count( $path_parts ) - 1 ];
				$posts = get_posts(
					array(
						'name'        => $slug,
						'post_type'   => 'post',
						'post_status' => 'publish',
						'numberposts' => 1,
					)
				);
				$post  = $posts[0] ?? null;
				if ( $post ) {
					$wp_query     = new WP_Query(
						array(
							'p'         => $post->ID,
							'post_type' => 'post',
						)
					);
					$wp_the_query = $wp_query;
				} else {
					$wp_query     = new WP_Query( array( 'post__in' => array( 0 ) ) );
					$wp_the_query = $wp_query;
				}
			}
		}
	}

	$body_classes = array( 'home', 'blog' );
	$title        = get_bloginfo( 'name' );
	$post_id      = null;

	if ( $wp_query->is_singular() && $wp_query->have_posts() ) {
		$wp_query->the_post();
		$post_id      = get_the_ID();
		$title        = get_the_title();
		$body_classes = get_body_class();
		$wp_query->rewind_posts();
	} elseif ( $wp_query->is_search() ) {
		$body_classes = array( 'search-results', 'search' );
		$title        = sprintf(
			/* translators: %s: search query. */
			__( 'Search: %s', 'interactivity-theme' ),
			get_search_query()
		);
	} elseif ( $wp_query->is_archive() ) {
		$body_classes = array( 'archive' );
		if ( $wp_query->is_category() ) {
			$body_classes[] = 'category';
			$title          = single_cat_title( '', false );
		} elseif ( $wp_query->is_tag() ) {
			$body_classes[] = 'tag';
			$title          = single_tag_title( '', false );
		}
	} elseif ( $page > 1 ) {
		$title = sprintf(
			/* translators: %d: page number. */
			__( 'Blog - Page %d', 'interactivity-theme' ),
			$page
		);
	}

	ob_start();
	if ( $wp_query->is_singular( 'page' ) ) {
		get_template_part( 'template-parts/route', 'page' );
	} elseif ( $wp_query->is_singular() ) {
		get_template_part( 'template-parts/route', 'single' );
	} else {
		get_template_part( 'template-parts/route', 'loop' );
	}
	$html = ob_get_clean();

	$wp_query     = $original_query;
	$wp_the_query = $original_the_query;
	// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited

	wp_reset_postdata();

	return rest_ensure_response(
		array(
			'html'        => $html,
			'title'       => $title,
			'bodyClasses' => $body_classes,
			'postId'      => $post_id,
		)
	);
}

/**
 * Return a single page by its full URL path (supports hierarchical pages).
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response|WP_Error Response or error.
 */
function interactivity_theme_page_by_path( $request ) {
	$path = trim( $request->get_param( 'path' ), '/' );
	if ( empty( $path ) ) {
		return new WP_Error( 'invalid_path', __( 'Invalid path.', 'interactivity-theme' ), array( 'status' => 400 ) );
	}

	$page = get_page_by_path( $path, OBJECT, 'page' );
	if ( ! $page || 'publish' !== $page->post_status ) {
		return new WP_Error( 'not_found', __( 'Page not found.', 'interactivity-theme' ), array( 'status' => 404 ) );
	}

	$data = array(
		'id'             => $page->ID,
		'title'          => array( 'rendered' => get_the_title( $page->ID ) ),
		'content'        => array( 'rendered' => apply_filters( 'the_content', $page->post_content ) ),
		'link'           => get_permalink( $page->ID ),
		'body_classes'   => array(),
		'comment_status' => get_post_field( 'comment_status', $page->ID ),
	);

	$post_classes         = get_post_class( '', $page );
	$data['body_classes'] = array_unique(
		array_merge(
			array( 'page', 'pageid-' . $page->ID ),
			$post_classes
		)
	);

	return rest_ensure_response( $data );
}

/**
 * Return enqueued styles and scripts for a given post.
 *
 * @param WP_REST_Request $request The request object.
 * @return WP_REST_Response Response with styles and scripts.
 */
function interactivity_theme_post_assets( $request ) {
	$post_id = $request->get_param( 'post_id' );
	$post    = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return rest_ensure_response(
			array(
				'styles'  => array(),
				'scripts' => array(),
			)
		);
	}

	global $wp_query, $wp_styles, $wp_scripts;

	$original_query = $wp_query;
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Intentional for asset discovery.
	$wp_query = new WP_Query(
		array(
			'p'         => $post_id,
			'post_type' => $post->post_type,
		)
	);
	$wp_query->the_post();

	do_action( 'wp_enqueue_scripts' );

	$styles  = array();
	$scripts = array();

	if ( isset( $wp_styles->queue ) ) {
		foreach ( $wp_styles->queue as $handle ) {
			if ( isset( $wp_styles->registered[ $handle ] ) && $wp_styles->registered[ $handle ]->src ) {
				$src = $wp_styles->registered[ $handle ]->src;
				if ( strpos( $src, '//' ) === false ) {
					$src = site_url( $src );
				}
				$styles[] = array(
					'handle' => $handle,
					'src'    => $src,
				);
			}
		}
	}

	if ( isset( $wp_scripts->queue ) ) {
		foreach ( $wp_scripts->queue as $handle ) {
			if ( isset( $wp_scripts->registered[ $handle ] ) && $wp_scripts->registered[ $handle ]->src ) {
				$src = $wp_scripts->registered[ $handle ]->src;
				if ( strpos( $src, '//' ) === false ) {
					$src = site_url( $src );
				}
				$scripts[] = array(
					'handle' => $handle,
					'src'    => $src,
				);
			}
		}
	}

	wp_reset_postdata();
	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restoring original.
	$wp_query = $original_query;

	return rest_ensure_response(
		array(
			'styles'  => $styles,
			'scripts' => $scripts,
		)
	);
}
