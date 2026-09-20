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

if (! function_exists('isBind9Active')) {
    function isBind9Active(): bool
    {
        // 1. Systemd service checks (named / bind9)
        /** @psalm-suppress ForbiddenCode */
        $namedStatus = shell_exec('systemctl is-active named 2>/dev/null');
        /** @psalm-suppress ForbiddenCode */
        $bind9Status = shell_exec('systemctl is-active bind9 2>/dev/null');

        if (
            (is_string($namedStatus) && trim($namedStatus) === 'active')
            || (is_string($bind9Status) && trim($bind9Status) === 'active')
        ) {
            return true;
        }

        // 2. Process table checks (Docker / LXC / non-systemd environments)
        /** @psalm-suppress ForbiddenCode */
        $pgrep = shell_exec('pgrep -x named 2>/dev/null || pidof named 2>/dev/null');
        // 3. RNDC control channel check
        /** @psalm-suppress ForbiddenCode */
        $rndc = shell_exec('rndc status 2>/dev/null');

        return (is_string($pgrep) && trim($pgrep) !== '')
            || (is_string($rndc) && str_contains($rndc, 'server is up and running'));
    }
}
