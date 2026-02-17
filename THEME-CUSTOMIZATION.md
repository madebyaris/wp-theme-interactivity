# Theme Customization Guide

The Interactivity Theme uses CSS custom properties (design tokens) so child themes and the Customizer can override styles without editing core files.

## Design Tokens

Override these variables in your child theme's `style.css` or via the Customizer's Additional CSS:

```css
:root {
    /* Colors */
    --theme-color-primary: #0073aa;
    --theme-color-text: #333;
    --theme-color-text-muted: #666;
    --theme-color-bg: #f5f5f5;
    --theme-color-surface: #fff;
    --theme-color-border: #ddd;
    --theme-color-border-light: #eee;
    --theme-color-footer-bg: #333;
    --theme-color-primary-hover: #005d8a;

    /* Typography */
    --theme-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --theme-font-size-base: 16px;
    --theme-line-height: 1.5;

    /* Spacing & layout */
    --theme-spacing-unit: 1rem;
    --theme-container-max: 1200px;
    --theme-container-padding: 20px;

    /* Shadows */
    --theme-shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
    --theme-shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --theme-shadow-card: 0 2px 4px rgba(0, 0, 0, 0.05);
}
```

## Example: Child Theme Override

In your child theme's `style.css`:

```css
/* Override primary color and footer */
:root {
    --theme-color-primary: #2563eb;
    --theme-color-footer-bg: #1e293b;
}
```

## Semantic Classes

The theme uses consistent class names for targeting:

- `.entry-content` – Post/page content
- `.post-card` – Archive list item
- `[data-theme-route-mount]` – SPA route content wrapper (for transitions)
