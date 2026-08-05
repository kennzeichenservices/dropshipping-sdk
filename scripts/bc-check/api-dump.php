<?php

declare(strict_types=1);

/**
 * Dumps the public API surface of a checkout so two versions can be diffed.
 * Usage: php api-dump.php /path/to/src
 */
$srcPath = rtrim($argv[1], '/');

spl_autoload_register(static function (string $class) use ($srcPath): void {
    if (!str_starts_with($class, 'Dropshipping\\')) {
        return;
    }
    $file = $srcPath . '/' . str_replace('\\', '/', substr($class, 13)) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

$classes = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcPath));
foreach ($it as $f) {
    if (!$f instanceof SplFileInfo || $f->getExtension() !== 'php') {
        continue;
    }
    $rel = substr($f->getPathname(), strlen($srcPath) + 1, -4);
    $classes[] = 'Dropshipping\\' . str_replace('/', '\\', $rel);
}
sort($classes);

function typeOf(?ReflectionType $t): string
{
    return $t === null ? 'mixed' : (string) $t;
}

foreach ($classes as $class) {
    if (!class_exists($class) && !interface_exists($class) && !enum_exists($class)) {
        continue;
    }
    $r = new ReflectionClass($class);

    $kind = $r->isInterface() ? 'interface' : ($r->isEnum() ? 'enum' : 'class');
    $mods = ($r->isFinal() ? 'final ' : '') . ($r->isReadOnly() ? 'readonly ' : '');
    echo "{$mods}{$kind} {$class}\n";

    if ($r->isEnum()) {
        foreach ((new ReflectionEnum($class))->getCases() as $c) {
            echo "  case {$c->getName()} = " . var_export($c->getBackingValue(), true) . "\n";
        }
    }

    foreach ($r->getProperties(ReflectionProperty::IS_PUBLIC) as $p) {
        if ($p->getDeclaringClass()->getName() !== $class) {
            continue;
        }
        echo '  prop ' . ($p->isReadOnly() ? 'readonly ' : '') . typeOf($p->getType()) . " \${$p->getName()}\n";
    }

    $methods = $r->getMethods(ReflectionMethod::IS_PUBLIC);
    usort($methods, static fn ($a, $b) => $a->getName() <=> $b->getName());
    foreach ($methods as $m) {
        if ($m->getDeclaringClass()->getName() !== $class) {
            continue;
        }
        $params = [];
        foreach ($m->getParameters() as $p) {
            $params[] = typeOf($p->getType()) . ' $' . $p->getName()
                . ($p->isDefaultValueAvailable() ? ' = ' . str_replace("\n", '', var_export($p->getDefaultValue(), true)) : '')
                . ($p->isOptional() ? '' : ' [REQUIRED]');
        }
        echo '  ' . ($m->isStatic() ? 'static ' : '') . "{$m->getName()}(" . implode(', ', $params) . '): '
            . typeOf($m->getReturnType()) . "\n";
    }
    echo "\n";
}
