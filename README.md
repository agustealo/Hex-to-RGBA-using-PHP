# Hex to RGB/RGBA with PHP

A small, framework-agnostic tutorial and utility for turning CSS hexadecimal colors into validated RGB/RGBA values with PHP.

The original version of this repository demonstrated a useful idea: take a hex color on the server and expose its RGB channels so the same color can be reused at different opacity levels. The web platform has moved forward since then, so this version teaches the same concept using modern PHP and modern CSS without pretending PHP is required for every transparency use case.

## What this tutorial covers

- `#RGB`, `#RGBA`, `#RRGGBB`, and `#RRGGBBAA`
- strict validation instead of silently accepting malformed colors
- extracting numeric RGBA components
- preserving an alpha channel embedded in 4- or 8-digit hex
- overriding alpha from PHP when needed
- modern CSS Color 4 output: `rgb(37 99 235 / 0.25)`
- legacy `rgba(...)` output when you explicitly need it
- safe server-generated CSS variables
- a zero-dependency test runner
- an interactive standalone demo

## Requirements

- PHP 8.0 or newer
- no framework
- no Composer packages

`str_starts_with()` is used by the utility and is available in PHP 8+.

## Why convert colors in PHP today?

Modern CSS already supports alpha directly:

```css
color: #2563eb80;
background: rgb(37 99 235 / 0.5);
```

So you should not round-trip a hard-coded CSS color through PHP just to make it transparent.

PHP conversion becomes useful when a color originates on the server, for example:

- application configuration
- database-backed user preferences
- tenant or brand settings
- generated email or document styles
- API payloads that must be normalized before rendering
- server-side design tokens

In those cases, validation and normalization matter just as much as conversion.

## The utility

The complete implementation lives in [`hex2rgba.php`](hex2rgba.php).

```php
<?php

declare(strict_types=1);

require __DIR__ . '/hex2rgba.php';

echo hex2rgb('#2563eb');
// 37, 99, 235

echo hex2rgba('#2563eb', 0.25);
// rgba(37, 99, 235, 0.25)

echo hex2css('#2563eb', 0.25);
// rgb(37 99 235 / 0.25)
```

The modern CSS form is preferred for newly generated CSS:

```php
$color = hex2css('#2563eb', 0.25);
```

Result:

```css
rgb(37 99 235 / 0.25)
```

## Supported hexadecimal formats

| Input | Meaning | Normalized channels |
| --- | --- | --- |
| `#09f` | short RGB | `0, 153, 255` |
| `#09f8` | short RGBA | `0, 153, 255` + embedded alpha |
| `#0099ff` | RGB | `0, 153, 255` |
| `#0099ff88` | RGBA | `0, 153, 255` + embedded alpha |

The leading `#` is optional when passing a value to the PHP utility, but generated CSS examples use normal CSS syntax with `#`.

## Embedded alpha versus an explicit alpha

Four- and eight-digit hex colors already contain alpha information.

```php
echo hex2css('#2563eb80');
// rgb(37 99 235 / 0.502)
```

You can deliberately override it:

```php
echo hex2css('#2563eb80', 0.2);
// rgb(37 99 235 / 0.2)
```

Three- and six-digit colors default to fully opaque when no alpha is supplied:

```php
echo hex2css('#2563eb');
// rgb(37 99 235 / 1)
```

## Reading the numeric channels

When you need data rather than a CSS string, use `hex2rgba_components()`:

```php
$color = hex2rgba_components('#2563ebcc');

var_export($color);
```

Output:

```php
[
    'red'       => 37,
    'green'     => 99,
    'blue'      => 235,
    'alpha'     => 0.8,
    'has_alpha' => true,
]
```

This is useful when the channels feed another renderer, serializer, image operation, or CSS token generator.

## Generate CSS variables from server-side colors

Suppose an application stores a brand color in configuration:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/hex2rgba.php';

$brand = '#2563eb';
$brandSoft = hex2css($brand, 0.12);
$brandHover = hex2css($brand, 0.85);
?>

<style>
:root {
    --brand: <?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>;
    --brand-soft: <?= htmlspecialchars($brandSoft, ENT_QUOTES, 'UTF-8') ?>;
    --brand-hover: <?= htmlspecialchars($brandHover, ENT_QUOTES, 'UTF-8') ?>;
}
</style>
```

Then ordinary CSS consumes the generated values:

```css
.card {
    border: 1px solid var(--brand);
    background: var(--brand-soft);
}

.card a {
    color: var(--brand);
}

.card a:hover {
    color: var(--brand-hover);
}
```

The important boundary is that the PHP utility validates the color before it becomes CSS.

## Invalid input fails loudly

The original implementation treated almost anything longer than three characters as six-digit input. That can produce surprising values from malformed data.

The current implementation accepts only valid 3-, 4-, 6-, or 8-digit hexadecimal color strings:

```php
try {
    echo hex2css('#12-not-a-color');
} catch (InvalidArgumentException $exception) {
    echo $exception->getMessage();
}
```

Alpha must also be finite and between `0` and `1`:

```php
hex2css('#2563eb', 1.5);
// throws InvalidArgumentException
```

## `rgb()` or `rgba()`?

For modern CSS, prefer:

```css
rgb(37 99 235 / 0.25)
```

The `rgba()` function is now effectively an alias of `rgb()` in CSS. This repository keeps `hex2rgba()` because the project name and historical API are useful, while `hex2css()` emits the cleaner modern syntax for new code.

## Run the tests

No test framework is required:

```bash
php tests/run.php
```

The test runner covers:

- short and long RGB input
- short and long RGBA input
- embedded alpha
- explicit alpha override
- modern and legacy CSS output
- case normalization
- invalid lengths
- invalid characters
- invalid alpha ranges

## Run the interactive demo

From the repository root:

```bash
php -S 127.0.0.1:8080 -t demo
```

Open:

```text
http://127.0.0.1:8080
```

Change the hex value and alpha level, submit the form, and the page will show the normalized channels plus both modern and legacy CSS forms.

## API reference

### `normalize_hex_color(string $hex): string`

Validates the input, expands 3/4-digit syntax, removes the optional `#`, and returns lowercase 6/8-digit hex.

### `hex2rgba_components(string $hex): array`

Returns red, green, blue, normalized alpha, and whether alpha existed in the original input.

### `hex2rgb(string $hex): string`

Preserves the original tutorial API and returns comma-separated RGB channels.

### `hex2rgba(string $hex, ?float $alpha = null): string`

Returns legacy-compatible `rgba(r, g, b, a)` syntax.

### `hex2css(string $hex, ?float $alpha = null): string`

Returns modern `rgb(r g b / a)` syntax.

## A small design note

If your source color already lives entirely in CSS, keep the transformation in CSS. If PHP owns the color as application data, validate it in PHP and emit a normalized CSS value. That keeps responsibility at the layer where the data actually lives.

## License

See [`LICENSE`](LICENSE).

---

Created by [@agustealo](https://github.com/agustealo).
