# WordPress theme integration

This is a project-level example of using the repository's framework-agnostic PHP color utility inside a WordPress theme.

The repository itself is **not WordPress-specific**. WordPress is simply one realistic place where a server-owned color may need to become reusable CSS.

## Project layout

```text
wp-content/
└── themes/
    └── your-theme/
        ├── functions.php
        ├── style.css
        └── inc/
            └── hex2rgba.php
```

Copy the repository's [`hex2rgba.php`](../../hex2rgba.php) into your theme's `inc/` directory, then use the supplied [`functions.php`](functions.php) example.

## What the example does

The example deliberately keeps responsibilities separated:

1. PHP owns and validates the server-side color values.
2. `hex2rgba.php` normalizes the values and creates translucent variants.
3. WordPress enqueues the theme stylesheet normally.
4. `wp_add_inline_style()` attaches only the generated CSS custom properties to that registered stylesheet.
5. Ordinary CSS consumes those properties throughout the theme.

That produces output conceptually equivalent to:

```css
:root {
    --theme-primary: #2563eb;
    --theme-primary-soft: rgb(37 99 235 / 0.12);
    --theme-primary-hover: rgb(37 99 235 / 0.88);
    --theme-accent: #f97316;
    --theme-accent-soft: rgb(249 115 22 / 0.16);
}
```

Your stylesheet can then remain ordinary CSS:

```css
.wp-block-button__link {
    background: var(--theme-primary);
}

.wp-block-button__link:hover,
.wp-block-button__link:focus-visible {
    background: var(--theme-primary-hover);
}

.notice {
    border-left: 4px solid var(--theme-accent);
    background: var(--theme-accent-soft);
}
```

See [`style.css`](style.css) for the full consumer example.

## Where the values might come from

The sample hard-codes two colors so the data flow is obvious, but in a real project the values could come from:

- a theme setting
- an options page
- a customizer-era setting in an older theme
- a database-backed brand profile
- multisite/tenant configuration
- a remote configuration source that has already been authorized by the application

The important part is that the value is validated before it is emitted into CSS.

## Why not print a `<style>` block in `header.php`?

Because the stylesheet already has an owner. Attaching generated CSS to the registered stylesheet keeps the dependency explicit and avoids scattering presentation output through templates.

The color conversion utility still knows nothing about WordPress. Only this integration layer knows about functions such as `wp_enqueue_style()` and `wp_add_inline_style()`.

## Plugin usage

The same pattern works in a plugin. Replace `get_stylesheet_uri()` with the URL of the plugin stylesheet and attach the generated variables to that plugin style handle.

The converter itself does not change.
