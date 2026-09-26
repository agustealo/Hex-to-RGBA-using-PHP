#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SCREENSHOT_DIR="$ROOT_DIR/docs/media/screenshots"
GIF_DIR="$ROOT_DIR/docs/media/gifs"
PORT="${DOC_MEDIA_PORT:-8765}"
BASE_URL="http://127.0.0.1:${PORT}"

mkdir -p "$SCREENSHOT_DIR" "$GIF_DIR"

PHP_PID=""
cleanup() {
    if [[ -n "$PHP_PID" ]]; then
        kill "$PHP_PID" 2>/dev/null || true
    fi
}
trap cleanup EXIT

php -S "127.0.0.1:${PORT}" -t "$ROOT_DIR/demo" >"${RUNNER_TEMP:-/tmp}/hex-rgba-demo.log" 2>&1 &
PHP_PID=$!

for _ in {1..30}; do
    if curl --fail --silent --show-error "$BASE_URL/" >/dev/null; then
        break
    fi
    sleep 0.25
done

curl --fail --silent --show-error "$BASE_URL/" >/dev/null

CHROME_BIN="${CHROME_BIN:-}"
if [[ -z "$CHROME_BIN" ]]; then
    for candidate in google-chrome chromium chromium-browser; do
        if command -v "$candidate" >/dev/null 2>&1; then
            CHROME_BIN="$(command -v "$candidate")"
            break
        fi
    done
fi

if [[ -z "$CHROME_BIN" ]]; then
    echo "Chrome/Chromium is required to capture documentation media." >&2
    exit 1
fi

capture() {
    local output="$1"
    local url="$2"

    "$CHROME_BIN" \
        --headless=new \
        --no-sandbox \
        --disable-gpu \
        --disable-dev-shm-usage \
        --hide-scrollbars \
        --window-size=1440,1000 \
        --screenshot="$output" \
        "$url"
}

capture "$SCREENSHOT_DIR/demo-default.png" "$BASE_URL/"
capture "$SCREENSHOT_DIR/demo-converted.png" "$BASE_URL/?hex=%23ff6b35&alpha=0.42"
capture "$SCREENSHOT_DIR/demo-validation.png" "$BASE_URL/?hex=%2312-not-a-color&alpha=0.25"

if command -v magick >/dev/null 2>&1; then
    magick \
        -delay 120 -loop 0 \
        "$SCREENSHOT_DIR/demo-default.png" \
        "$SCREENSHOT_DIR/demo-converted.png" \
        "$SCREENSHOT_DIR/demo-validation.png" \
        -resize 960x \
        -layers Optimize \
        "$GIF_DIR/conversion-flow.gif"
elif command -v convert >/dev/null 2>&1; then
    convert \
        -delay 120 -loop 0 \
        "$SCREENSHOT_DIR/demo-default.png" \
        "$SCREENSHOT_DIR/demo-converted.png" \
        "$SCREENSHOT_DIR/demo-validation.png" \
        -resize 960x \
        -layers Optimize \
        "$GIF_DIR/conversion-flow.gif"
else
    echo "ImageMagick is required to build the documentation GIF." >&2
    exit 1
fi

printf 'Captured documentation media:\n'
find "$ROOT_DIR/docs/media" -type f -maxdepth 3 -print | sort
