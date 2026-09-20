<?php

declare(strict_types=1);

namespace App\Services\System;

use PDO;

final class DatabaseOptimizer
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function optimize(): array
    {
        $pageCount = $this->fetchInt('PRAGMA page_count');
        $pageSize  = $this->fetchInt('PRAGMA page_size');
        $before    = $pageCount * $pageSize;

        $this->pdo->exec('PRAGMA optimize');
        $this->pdo->exec('VACUUM');
        $this->pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');

        $pageCountAfter = $this->fetchInt('PRAGMA page_count');
        $after          = $pageCountAfter * $pageSize;

        return [
            'bytes_before'    => $before,
            'bytes_after'     => $after,
            'bytes_reclaimed' => max(0, $before - $after),
        ];
    }

    private function fetchInt(string $query): int
    {
        $statement = $this->pdo->query($query);
        if ($statement === false) {
            return 0;
        }

        $value = $statement->fetchColumn();

        return is_numeric($value) ? (int) $value : 0;
    }
}
