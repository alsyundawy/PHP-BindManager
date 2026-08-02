<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
    /**
     * @param array<string, mixed> $data
     */
    public static function render(string $view, array $data = []): string
    {
        $file = Path::resources('Views/' . str_replace('.', '/', $view) . '.php');

        if (! is_file($file)) {
            return 'View not found.';
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;

        return (string) ob_get_clean();
    }
}
