<?php

declare(strict_types=1);

/**
 * Normalize a CSS hexadecimal color.
 *
 * Supported forms: #RGB, #RGBA, #RRGGBB, and #RRGGBBAA.
 *
 * @throws InvalidArgumentException When the color is not valid hexadecimal CSS color syntax.
 */
function normalize_hex_color(string $hex): string
{
    $hex = trim($hex);

    if (str_starts_with($hex, '#')) {
        $hex = substr($hex, 1);
    }

    if (! preg_match('/\A(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})\z/', $hex)) {
        throw new InvalidArgumentException(
            'Hex color must use #RGB, #RGBA, #RRGGBB, or #RRGGBBAA syntax.'
        );
    }

    if (strlen($hex) === 3 || strlen($hex) === 4) {
        $expanded = '';

        foreach (str_split($hex) as $digit) {
            $expanded .= $digit . $digit;
        }

        $hex = $expanded;
    }

    return strtolower($hex);
}

/**
 * Convert a hexadecimal color into numeric RGBA components.
 *
 * @return array{red:int, green:int, blue:int, alpha:float, has_alpha:bool}
 */
function hex2rgba_components(string $hex): array
{
    $normalized = normalize_hex_color($hex);
    $has_alpha = strlen($normalized) === 8;

    if (! $has_alpha) {
        $normalized .= 'ff';
    }

    return [
        'red'       => hexdec(substr($normalized, 0, 2)),
        'green'     => hexdec(substr($normalized, 2, 2)),
        'blue'      => hexdec(substr($normalized, 4, 2)),
        'alpha'     => hexdec(substr($normalized, 6, 2)) / 255,
        'has_alpha' => $has_alpha,
    ];
}

/**
 * Keep the original tutorial API: return comma-separated RGB channels.
 */
function hex2rgb(string $hex): string
{
    $color = hex2rgba_components($hex);

    return sprintf('%d, %d, %d', $color['red'], $color['green'], $color['blue']);
}

/**
 * Convert a hexadecimal color to legacy-compatible rgba() CSS syntax.
 *
 * When $alpha is null, #RGBA / #RRGGBBAA input keeps its embedded alpha.
 * Six- and three-digit colors default to fully opaque.
 */
function hex2rgba(string $hex, ?float $alpha = null): string
{
    $color = hex2rgba_components($hex);
    $resolved_alpha = $alpha ?? $color['alpha'];

    validate_alpha($resolved_alpha);

    return sprintf(
        'rgba(%d, %d, %d, %s)',
        $color['red'],
        $color['green'],
        $color['blue'],
        format_alpha($resolved_alpha, $alpha === null && $color['has_alpha'])
    );
}

/**
 * Convert a hexadecimal color to modern CSS Color 4 rgb() syntax.
 *
 * Example: rgb(37 99 235 / 0.25)
 */
function hex2css(string $hex, ?float $alpha = null): string
{
    $color = hex2rgba_components($hex);
    $resolved_alpha = $alpha ?? $color['alpha'];

    validate_alpha($resolved_alpha);

    return sprintf(
        'rgb(%d %d %d / %s)',
        $color['red'],
        $color['green'],
        $color['blue'],
        format_alpha($resolved_alpha, $alpha === null && $color['has_alpha'])
    );
}

/**
 * Validate a CSS alpha value.
 */
function validate_alpha(float $alpha): void
{
    if (! is_finite($alpha) || $alpha < 0.0 || $alpha > 1.0) {
        throw new InvalidArgumentException('Alpha must be a finite number between 0 and 1.');
    }
}

/**
 * Format an alpha value without changing caller-supplied precision.
 *
 * Alpha decoded from #RGBA / #RRGGBBAA has only 8-bit source precision, so
 * three decimal places are sufficient for that representation. Explicit
 * float arguments are serialized losslessly enough to round-trip as floats.
 */
function format_alpha(float $alpha, bool $from_embedded_byte = false): string
{
    if ($from_embedded_byte) {
        $formatted = number_format($alpha, 3, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '' ? '0' : $formatted;
    }

    $formatted = json_encode($alpha, JSON_THROW_ON_ERROR);

    return $formatted;
}
