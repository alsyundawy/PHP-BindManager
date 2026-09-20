<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\PathNotBootstrappedException;

final class Path
{
    private static ?string $basePath = null;

    public static function bootstrap(string $basePath): void
    {
        self::$basePath = rtrim($basePath, '/');
    }

    public static function base(string $path = ''): string
    {
        return self::join(self::$basePath ?? throw new PathNotBootstrappedException('Path not bootstrapped.'), $path);
    }

    public static function config(string $path = ''): string
    {
        return self::join(self::base('Config'), $path);
    }

    public static function routes(string $path = ''): string
    {
        return self::join(self::base('Routes'), $path);
    }

    public static function storage(string $path = ''): string
    {
        return self::join(self::base('Storage'), $path);
    }

    public static function resources(string $path = ''): string
    {
        return self::join(self::base('Resources'), $path);
    }

    private static function join(string $base, string $path): string
    {
        return rtrim($base, '/') . ($path !== '' ? '/' . ltrim($path, '/') : '');
    }
}
