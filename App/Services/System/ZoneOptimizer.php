<?php

declare(strict_types=1);

namespace App\Services\System;

final class ZoneOptimizer
{
    public function normalize(string $zoneText): string
    {
        // Normalize line endings to LF
        $zoneText = str_replace(["\r\n", "\r"], "\n", $zoneText);
        // Strip trailing whitespace per line
        $zoneText = preg_replace('/[ \t]+$/m', '', $zoneText) ?? $zoneText;
        // Collapse 3+ consecutive blank lines to 2
        $zoneText = preg_replace("/\n{3,}/", "\n\n", $zoneText) ?? $zoneText;

        return rtrim($zoneText) . "\n";
    }
}
