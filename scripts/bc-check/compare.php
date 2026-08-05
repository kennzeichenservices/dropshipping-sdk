<?php

declare(strict_types=1);

/**
 * Classifies the difference between two hydration dumps.
 *
 * Usage: php compare.php old.jsonl new.jsonl
 * Exits 1 when a regression is found, so a release can be gated on it.
 */

/**
 * @return array<string, array<string, mixed>>
 */
function load(string $path): array
{
    $out = [];

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        // The old checkout may emit PHP warnings on stdout; only JSON lines are results.
        if (!str_starts_with($line, '{')) {
            continue;
        }

        /** @var array<string, mixed> $row */
        $row = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
        $out[(string) $row['label']] = $row;
    }

    return $out;
}

$old = load($argv[1]);
$new = load($argv[2]);

/** @var array<string, string> $accepted */
$accepted = require __DIR__ . '/accepted.php';

$same = [];
$intended = [];
$regressions = [];
$acknowledged = [];
$missing = [];

foreach ($old as $label => $o) {
    if (!isset($new[$label])) {
        $missing[] = $label;

        continue;
    }

    $n = $new[$label];

    if ($o === $n) {
        $same[] = $label;

        continue;
    }

    if (isset($accepted[$label])) {
        $acknowledged[] = [$label, $accepted[$label]];

        continue;
    }

    if ($o['outcome'] === 'ok' && $n['outcome'] === 'ok') {
        if ($o['value'] === $n['value']) {
            $same[] = $label;
        } else {
            $regressions[] = [$label, 'VALUE CHANGED', json_encode($o['value']), json_encode($n['value'])];
        }
    } elseif ($o['outcome'] === 'throw' && $n['outcome'] === 'throw') {
        if ($o['class'] === $n['class']) {
            // Same failure, different wording — messages are allowed to improve.
            $same[] = $label;
        } else {
            $intended[] = [$label, $o['class'], $n['class']];
        }
    } elseif ($o['outcome'] === 'ok' && $n['outcome'] === 'throw') {
        $regressions[] = [$label, 'NOW THROWS', 'ok', $n['class'] . ': ' . $n['message']];
    } else {
        $intended[] = [$label, $o['class'] . ' (threw)', 'ok — now tolerated'];
    }
}

$newOnly = array_diff(array_keys($new), array_keys($old));

printf("  identical              %d / %d\n", count($same), count($old));
printf("  intentionally changed  %d\n", count($intended));
printf("  accepted (see accepted.php)  %d\n", count($acknowledged));
printf("  regressions            %d\n", count($regressions));

if ($missing !== []) {
    printf("  missing in new         %d\n", count($missing));
}

if ($newOnly !== []) {
    printf("  new corpus entries     %d (not present in the baseline)\n", count($newOnly));
}

if ($acknowledged !== []) {
    echo "\n  Accepted changes:\n";

    foreach ($acknowledged as [$label, $reason]) {
        printf("    %s\n        %s\n", $label, $reason);
    }
}

if ($intended !== []) {
    echo "\n  Intentional changes — review each before release:\n";

    foreach ($intended as [$label, $from, $to]) {
        printf("    %s\n        %s  ->  %s\n", $label, $from, $to);
    }
}

if ($regressions !== []) {
    echo "\n  REGRESSIONS:\n";

    foreach ($regressions as [$label, $kind, $before, $after]) {
        printf("    %s  [%s]\n        before: %s\n        after:  %s\n", $label, $kind, mb_substr((string) $before, 0, 200), mb_substr((string) $after, 0, 200));
    }

    exit(1);
}

exit(0);
