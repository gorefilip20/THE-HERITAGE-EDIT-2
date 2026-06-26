<?php

declare(strict_types=1);

namespace HeritageEdit\Core;

/**
 * Minimal .env file parser — no dependencies.
 * Supports: KEY=value, KEY="quoted value", KEY='single quoted', # comments, export KEY=value
 */
final class Env
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and blank lines
            if ($line === '' || str_starts_with($line, '#')) continue;

            // Strip leading "export "
            if (str_starts_with($line, 'export ')) {
                $line = substr($line, 7);
            }

            // Must contain =
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip inline comments (unquoted only)
            if (!in_array($value[0] ?? '', ['"', "'"], true)) {
                if (($pos = strpos($value, ' #')) !== false) {
                    $value = trim(substr($value, 0, $pos));
                }
            }

            // Unquote
            $value = self::unquote($value);

            // Expand ${VAR} references
            $value = preg_replace_callback('/\$\{([A-Z_][A-Z0-9_]*)\}/', function ($m) {
                return $_ENV[$m[1]] ?? getenv($m[1]) ?: '';
            }, $value);

            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key]  = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    private static function unquote(string $value): string
    {
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[-1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
                if ($first === '"') {
                    $value = stripcslashes($value);
                }
            }
        }
        return $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public static function require(string ...$keys): void
    {
        foreach ($keys as $key) {
            if (empty($_ENV[$key]) && !getenv($key)) {
                throw new \RuntimeException("Required environment variable [$key] is not set.");
            }
        }
    }
}
