<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$envFile = dirname(__DIR__) . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        if (str_contains($line, '=')) {
            putenv(trim($line));
        }
    }
}

if (getenv('DROPSHIPPING_API_HOST')) {
    define('KS_DROPSHIPPING_DEBUG', true);
    define('KS_DROPSHIPPING_DEBUG_FILE', dirname(__DIR__) . '/debug.log');
}
