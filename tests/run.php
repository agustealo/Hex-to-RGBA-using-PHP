<?php

declare(strict_types=1);

require dirname(__DIR__) . '/hex2rgba.php';

$tests = [];

function test(string $name, callable $callback): void
{
    global $tests;
    $tests[] = [$name, $callback];
}

function assert_same(mixed $expected, mixed $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            sprintf(
                "Expected %s, got %s",
                var_export($expected, true),
                var_export($actual, true)
            )
        );
    }
}

function assert_throws(string $exceptionClass, callable $callback): void
{
    try {
        $callback();
    } catch (Throwable $throwable) {
        if ($throwable instanceof $exceptionClass) {
            return;
        }

        throw new RuntimeException(
            sprintf('Expected %s, got %s', $exceptionClass, $throwable::class),
            0,
            $throwable
        );
    }

    throw new RuntimeException(sprintf('Expected %s to be thrown.', $exceptionClass));
}

test('normalizes short RGB', static function (): void {
    assert_same('0099ff', normalize_hex_color('#09f'));
});

test('normalizes short RGBA', static function (): void {
    assert_same('0099ff88', normalize_hex_color('#09f8'));
});

test('normalizes uppercase input', static function (): void {
    assert_same('aabbcc', normalize_hex_color('AABBCC'));
});

test('converts short RGB', static function (): void {
    assert_same('0, 153, 255', hex2rgb('#09f'));
});

test('converts long RGB', static function (): void {
    assert_same('37, 99, 235', hex2rgb('#2563eb'));
});

test('returns modern CSS syntax', static function (): void {
    assert_same('rgb(37 99 235 / 0.25)', hex2css('#2563eb', 0.25));
});

test('returns legacy rgba syntax', static function (): void {
    assert_same('rgba(37, 99, 235, 0.25)', hex2rgba('#2563eb', 0.25));
});

test('preserves embedded alpha', static function (): void {
    assert_same('rgb(37 99 235 / 0.502)', hex2css('#2563eb80'));
});

test('explicit alpha overrides embedded alpha', static function (): void {
    assert_same('rgb(37 99 235 / 0.2)', hex2css('#2563eb80', 0.2));
});

test('components expose alpha provenance', static function (): void {
    $components = hex2rgba_components('#09f8');

    assert_same(0, $components['red']);
    assert_same(153, $components['green']);
    assert_same(255, $components['blue']);
    assert_same(true, $components['has_alpha']);
});

test('rejects invalid length', static function (): void {
    assert_throws(InvalidArgumentException::class, static fn () => normalize_hex_color('#12'));
});

test('rejects invalid characters', static function (): void {
    assert_throws(InvalidArgumentException::class, static fn () => normalize_hex_color('#12zz34'));
});

test('rejects alpha below zero', static function (): void {
    assert_throws(InvalidArgumentException::class, static fn () => hex2css('#2563eb', -0.1));
});

test('rejects alpha above one', static function (): void {
    assert_throws(InvalidArgumentException::class, static fn () => hex2css('#2563eb', 1.1));
});

$passed = 0;
$failed = 0;

foreach ($tests as [$name, $callback]) {
    try {
        $callback();
        ++$passed;
        fwrite(STDOUT, "PASS  {$name}\n");
    } catch (Throwable $throwable) {
        ++$failed;
        fwrite(STDERR, "FAIL  {$name}\n      {$throwable->getMessage()}\n");
    }
}

fwrite(STDOUT, sprintf("\n%d passed, %d failed\n", $passed, $failed));

exit($failed === 0 ? 0 : 1);
