<?php
/**
 * WordPress theme integration example for Hex-to-RGBA-using-PHP.
 *
 * Copy hex2rgba.php into your theme, for example:
 *
 *     /wp-content/themes/your-theme/inc/hex2rgba.php
 *
 * Then load it from functions.php and expose validated server-side colors as
 * CSS custom properties attached to your enqueued stylesheet.
 */

declare(strict_types=1);

require_once __DIR__ . '/inc/hex2rgba.php';

/**
 * Enqueue the theme stylesheet and attach dynamic color tokens.
 */
function agustealo_enqueue_theme_styles(): void
{
    $style_handle = 'agustealo-theme';

    wp_enqueue_style(
        $style_handle,
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );

    /*
     * In a real project these values might come from theme settings,
     * an options page, a database record, or another trusted server-side
     * configuration source.
     */
    $primary_hex = '#2563eb';
    $accent_hex  = '#f97316';

    try {
        $primary       = '#' . normalize_hex_color($primary_hex);
        $primary_soft  = hex2css($primary_hex, 0.12);
        $primary_hover = hex2css($primary_hex, 0.88);
        $accent        = '#' . normalize_hex_color($accent_hex);
        $accent_soft   = hex2css($accent_hex, 0.16);
    } catch (InvalidArgumentException $exception) {
        /*
         * Do not emit invalid CSS when configuration is malformed.
         * In production you could also log the exception for administrators.
         */
        return;
    }

    $dynamic_css = sprintf(
        ':root{%s}',
        implode(
            '',
            [
                '--theme-primary:' . $primary . ';',
                '--theme-primary-soft:' . $primary_soft . ';',
                '--theme-primary-hover:' . $primary_hover . ';',
                '--theme-accent:' . $accent . ';',
                '--theme-accent-soft:' . $accent_soft . ';',
            ]
        )
    );

    wp_add_inline_style($style_handle, $dynamic_css);
}
add_action('wp_enqueue_scripts', 'agustealo_enqueue_theme_styles');
