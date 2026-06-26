<?php

declare(strict_types=1);

/**
 * PSR-4 autoloader — zero dependencies.
 * Maps HeritageEdit\ → src/
 */
final class Autoloader
{
    private static array $map = [
        'HeritageEdit\\' => __DIR__ . '/../../src/',
    ];

    public static function register(): void
    {
        spl_autoload_register(function (string $class): void {
            foreach (self::$map as $prefix => $base) {
                if (!str_starts_with($class, $prefix)) continue;
                $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
                $file     = $base . $relative . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }
}

Autoloader::register();
