<?php

declare(strict_types=1);

require dirname(__DIR__) . '/hex2rgba.php';

$inputHex = isset($_GET['hex']) ? trim((string) $_GET['hex']) : '#2563eb';
$inputAlpha = isset($_GET['alpha']) ? trim((string) $_GET['alpha']) : '0.25';
$error = null;
$result = null;

try {
    if ($inputAlpha === '' || ! is_numeric($inputAlpha)) {
        throw new InvalidArgumentException('Alpha must be a number between 0 and 1.');
    }

    $alpha = (float) $inputAlpha;
    $components = hex2rgba_components($inputHex);
    $normalized = normalize_hex_color($inputHex);

    $result = [
        'normalized' => '#' . $normalized,
        'red' => $components['red'],
        'green' => $components['green'],
        'blue' => $components['blue'],
        'embedded_alpha' => $components['has_alpha'] ? format_alpha($components['alpha']) : 'none',
        'modern' => hex2css($inputHex, $alpha),
        'legacy' => hex2rgba($inputHex, $alpha),
    ];
} catch (InvalidArgumentException $exception) {
    $error = $exception->getMessage();
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hex to RGB/RGBA with PHP</title>
    <style>
        :root {
            color-scheme: light dark;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: Canvas;
            color: CanvasText;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; padding: clamp(1.25rem, 4vw, 4rem); }
        main { width: min(920px, 100%); margin: 0 auto; }
        header { margin-bottom: 2rem; }
        h1 { margin: 0 0 .65rem; font-size: clamp(2rem, 7vw, 4.5rem); letter-spacing: -.055em; line-height: .95; }
        p { max-width: 68ch; line-height: 1.65; opacity: .78; }
        .layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.3fr); gap: 1rem; align-items: stretch; }
        .panel { border: 1px solid color-mix(in srgb, CanvasText 16%, transparent); border-radius: 1.25rem; padding: 1.25rem; background: color-mix(in srgb, Canvas 94%, CanvasText 6%); }
        label { display: grid; gap: .45rem; margin-bottom: 1rem; font-weight: 700; }
        input { width: 100%; border: 1px solid color-mix(in srgb, CanvasText 24%, transparent); border-radius: .75rem; padding: .8rem .9rem; font: inherit; background: Canvas; color: CanvasText; }
        button { border: 0; border-radius: .75rem; padding: .85rem 1rem; font: inherit; font-weight: 800; cursor: pointer; background: CanvasText; color: Canvas; }
        .swatch { min-height: 210px; border-radius: 1rem; border: 1px solid color-mix(in srgb, CanvasText 16%, transparent); margin-bottom: 1rem; display: grid; place-items: end start; padding: 1rem; }
        .swatch span { padding: .45rem .65rem; border-radius: .55rem; background: Canvas; color: CanvasText; font: 700 .85rem/1 ui-monospace, SFMono-Regular, Menlo, monospace; }
        dl { display: grid; grid-template-columns: max-content 1fr; gap: .6rem 1rem; margin: 0; }
        dt { opacity: .65; }
        dd { margin: 0; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; overflow-wrap: anywhere; }
        .code { margin-top: 1rem; display: grid; gap: .65rem; }
        code { display: block; padding: .8rem; border-radius: .7rem; background: color-mix(in srgb, CanvasText 8%, transparent); overflow-x: auto; }
        .error { border-color: currentColor; }
        .error strong { color: #d33; }
        footer { margin-top: 1rem; font-size: .9rem; opacity: .65; }
        @media (max-width: 720px) { .layout { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<main>
    <header>
        <h1>Hex → RGB(A)</h1>
        <p>A tiny standalone PHP playground. Enter a CSS hex color and alpha value; the conversion happens on the server using the same utility documented in the repository.</p>
    </header>

    <div class="layout">
        <form class="panel" method="get">
            <label>
                Hex color
                <input name="hex" value="<?= e($inputHex) ?>" autocomplete="off" spellcheck="false" placeholder="#2563eb">
            </label>
            <label>
                Alpha (0–1)
                <input name="alpha" value="<?= e($inputAlpha) ?>" inputmode="decimal" autocomplete="off" placeholder="0.25">
            </label>
            <button type="submit">Convert with PHP</button>
        </form>

        <?php if ($error !== null): ?>
            <section class="panel error" aria-live="polite">
                <strong>Invalid input</strong>
                <p><?= e($error) ?></p>
            </section>
        <?php elseif ($result !== null): ?>
            <section class="panel" aria-live="polite">
                <div class="swatch" style="background: <?= e($result['modern']) ?>">
                    <span><?= e($result['modern']) ?></span>
                </div>
                <dl>
                    <dt>Normalized</dt><dd><?= e($result['normalized']) ?></dd>
                    <dt>Red</dt><dd><?= (int) $result['red'] ?></dd>
                    <dt>Green</dt><dd><?= (int) $result['green'] ?></dd>
                    <dt>Blue</dt><dd><?= (int) $result['blue'] ?></dd>
                    <dt>Hex alpha</dt><dd><?= e($result['embedded_alpha']) ?></dd>
                </dl>
                <div class="code">
                    <code><?= e($result['modern']) ?></code>
                    <code><?= e($result['legacy']) ?></code>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <footer>Zero framework. Zero dependencies. The page intentionally submits to PHP so the tutorial demonstrates server-side conversion rather than hiding it behind JavaScript.</footer>
</main>
</body>
</html>
