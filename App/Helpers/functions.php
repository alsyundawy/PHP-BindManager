<?php

declare(strict_types=1);

if (! function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (! function_exists('asset')) {
    function asset(string $path): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (! function_exists('pbmNavActive')) {
    function pbmNavActive(string $path, string $currentPath): string
    {
        $active = ($currentPath === $path)
            || ($path !== '/' && str_starts_with($currentPath, $path));

        return $active ? ' is-active' : '';
    }
}
