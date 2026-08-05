<?php

declare(strict_types=1);

/**
 * Runs every corpus payload through <srcPath>'s DTOs and prints a normalised result
 * per line, so two checkouts can be diffed byte for byte.
 *
 * Usage: php harness.php /path/to/src /path/to/corpus.php
 */

$srcPath = rtrim($argv[1], '/');
$corpus = require $argv[2];

spl_autoload_register(static function (string $class) use ($srcPath): void {
    if (!str_starts_with($class, 'Dropshipping\\')) {
        return;
    }
    $rel = str_replace('\\', '/', substr($class, strlen('Dropshipping\\'))) . '.php';
    $file = $srcPath . '/' . $rel;
    if (is_file($file)) {
        require_once $file;
    }
});

/** Normalise any value into a stable, comparable structure. */
function norm(mixed $v): mixed
{
    if ($v instanceof BackedEnum) {
        return ['#enum' => $v::class, 'value' => $v->value];
    }
    if (is_object($v)) {
        $out = ['#class' => $v::class];
        foreach (get_object_vars($v) as $k => $pv) {
            $out[$k] = norm($pv);
        }
        return $out;
    }
    if (is_array($v)) {
        $out = [];
        foreach ($v as $k => $item) {
            // Keep the key so a list vs keyed-array difference is visible.
            $out[$k] = norm($item);
        }
        return $out;
    }
    return $v;
}

foreach ($corpus as [$label, $class, $payload]) {
    try {
        $result = $class::fromArray($payload);
        $line = ['label' => $label, 'outcome' => 'ok', 'value' => norm($result)];
    } catch (Throwable $e) {
        $line = [
            'label' => $label,
            'outcome' => 'throw',
            'class' => $e::class,
            // Message text is expected to improve; the diff focuses on class + shape.
            'message' => $e->getMessage(),
        ];
    }
    echo json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), "\n";
}
