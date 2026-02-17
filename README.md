# Interactivity Theme

A fast, interactive WordPress theme with SPA-like navigation and the Interactivity API for partial hydration. Content is server-rendered and fetched via REST API for client-side routing—no full page reloads on internal links.

## Features

- **SPA-like Navigation**: Client-side routing with server-rendered HTML. Internal links fetch content from `/theme/route-html` and replace the main content area without a full reload.
- **Interactivity API**: Uses WordPress 6.5+ Interactivity API for interactive blocks (accordion, counter, search).
- **Server-Side Rendering**: All content is rendered on the server for SEO and fast initial load.
- **4 Custom Blocks**:
  - **Navigation**: Responsive menu with SPA routing, mobile toggle, and route caching
  - **Search**: Live search with REST API integration
  - **Counter**: Simple increment/decrement counter
  - **Accordion**: Collapsible content sections
- **WooCommerce Compatible**: Includes fix for Order Attribution script conflict (CustomElementRegistry error).
- **Design Tokens**: CSS custom properties for easy customization—see [THEME-CUSTOMIZATION.md](THEME-CUSTOMIZATION.md).

## Requirements

- WordPress 6.5 or higher (or Gutenberg 17.5+)
- PHP 7.4+
- Node.js 18+ (for building block assets)

## Installation

1. **Download/Clone**: Place this theme folder in `wp-content/themes/`
2. **Activate**: Go to WordPress Admin → Appearance → Themes and activate "Interactivity Theme"
3. **Install Dependencies** (for block editor support):

```bash
cd wp-content/themes/interactivity-theme
npm install
```

4. **Build Assets**:

```bash
npm run build
```

5. **PHP Linting** (optional, requires [Composer](https://getcomposer.org/)):

```bash
composer install
npm run lint:php        # Check PHP against WordPress Coding Standards
npm run lint:php:fix    # Auto-fix fixable issues (phpcbf)
```

## How It Works

### SPA Navigation

- Internal link clicks are intercepted and trigger a fetch to `/theme/route-html?path=...`
- The endpoint returns server-rendered HTML, page title, and body classes
- Content is injected into `[data-theme-route-mount]` without a full reload
- **Caching**: Home, archives, search, category, and tag pages are cached (5 min). Single posts and pages are always fetched fresh so comments stay up to date.

### Template Hierarchy

- **Front page**: `front-page.php` (handles both "Your latest posts" and "A static page")
- **Single post**: `single.php` → `template-parts/route-single.php`
- **Single page**: `page.php` → `template-parts/route-page.php`
- **Archives, home, search**: `index.php`, `archive.php`, `search.php` → `template-parts/route-loop.php`

### REST Endpoints

| Endpoint | Purpose |
|----------|---------|
| `GET /wp/v2/theme/route-html?path=...` | Server-rendered HTML for a route |
| `GET /wp/v2/theme/global-styles` | Block editor global styles (CSS) |
| `GET /wp/v2/theme/post-assets?post_id=...` | Enqueued styles/scripts for a post |

## Linting

- **PHP**: `npm run lint:php` / `npm run lint:php:fix` (WPCS via Composer)
- **TypeScript/JavaScript**: `npm run lint:js` / `npm run lint:js:fix` (ESLint + TypeScript via @wordpress/scripts)
- **CSS**: `npm run lint:css`
- **All**: `npm run lint`

TypeScript support is enabled when the `typescript` package is installed. The WordPress ESLint config applies `@typescript-eslint` rules to `.ts` and `.tsx` files.

## Block Development

### Source vs Build

- **Source**: `blocks/*/` — TypeScript (`.ts`), PHP, CSS
- **Build**: `build/blocks/*/` — Compiled output used by WordPress

Blocks are built with `npm run build` (webpack).

### Navigation Block (SPA Router)

The navigation block uses vanilla TypeScript (no Interactivity API store) for the router. It:

- Intercepts internal link clicks via inline script in `wp_head`
- Fetches HTML from `route-html`, updates title and body classes
- Injects post-specific assets when navigating to singular content
- Supports popstate (browser back/forward)

### Interactivity API Blocks

Accordion, counter, and search use the Interactivity API:

- `data-wp-interactive` — Activates interactivity
- `data-wp-context` — Local state
- `data-wp-on--event` — Event handlers
- `data-wp-class--classname` — Toggle classes
- `data-wp-show` / `data-wp-hide` — Visibility

## WooCommerce Compatibility

The theme dequeues the WooCommerce Order Attribution script on non-checkout pages to prevent the `CustomElementRegistry: wc-order-attribution-inputs already used` error. Order attribution still works on checkout and cart.

## File Structure

```
interactivity-theme/
├── style.css                 # Main stylesheet
├── functions.php             # Theme functions, REST routes, WooCommerce fix
├── front-page.php            # Front page template
├── index.php                 # Main template
├── single.php                # Single post
├── page.php                  # Page template
├── archive.php               # Archive
├── search.php                # Search results
├── header.php, footer.php, sidebar.php, comments.php
├── template-parts/
│   ├── route-page.php        # Single page content
│   ├── route-single.php      # Single post content
│   └── route-loop.php       # Archive/home/search content
├── blocks/                   # Source (TypeScript, PHP)
│   ├── navigation/          # SPA router + menu
│   ├── search/
│   ├── counter/
│   └── accordion/
├── build/blocks/             # Compiled output
├── package.json
├── webpack.config.js
├── THEME-CUSTOMIZATION.md    # Design tokens
└── README.md
```

## Performance Tips

1. **Caching**: Archives and lists are cached client-side; singular content is always fresh.
2. **Minimal JS**: Only the navigation block and interactive blocks load JavaScript.
3. **Server Rendering**: All content comes from PHP—no client-side template building.

## Troubleshooting

### Blank content on refresh
- Ensure `template-parts/route-page.php`, `route-single.php`, and `route-loop.php` exist (not in a `route/` subfolder).
- Check that `front-page.php` exists if the front page is blank.

### Comments not loading on SPA navigation
- Single posts and pages are no longer cached; comments should load. Clear `localStorage` (key: `theme_route_cache`) if you see stale content.

### WooCommerce order-attribution error
- The theme automatically dequeues the script on non-checkout pages. If the error persists, ensure the theme’s WooCommerce fix in `functions.php` is active.

### Blocks not showing in editor
- Run `npm install && npm run build` to compile block assets.
- Verify WordPress 6.5+ is installed.

## Resources

- [Interactivity API Documentation](https://developer.wordpress.org/block-editor/reference-guides/interactivity-api)
- [WordPress Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [THEME-CUSTOMIZATION.md](THEME-CUSTOMIZATION.md) — Design tokens and overrides

## License

GNU General Public License v2 or later
