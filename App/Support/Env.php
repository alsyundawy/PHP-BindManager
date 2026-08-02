<?php

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\Dotenv\Dotenv;

final class Env
{
    public static function load(string $envFile): void
    {
        if (! is_file($envFile)) {
            return;
        }

        $dotenv = new Dotenv();
        $dotenv->usePutenv(true);
        $dotenv->load($envFile);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        return $value === false ? $default : $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
