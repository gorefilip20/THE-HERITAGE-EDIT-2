<?php

declare(strict_types=1);

namespace HeritageEdit\Core;

/**
 * UUID v4 generator using random_bytes — no dependencies.
 */
final class Uuid
{
    public static function v4(): string
    {
        $bytes = random_bytes(16);

        // Set version to 0100 (v4)
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        // Set variant to 10xxxxxx (RFC 4122)
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
