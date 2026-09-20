<?php

declare(strict_types=1);

namespace App\Services\System;

use PDO;

final class DatabaseMaintenanceService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function optimize(): void
    {
        $this->pdo->exec('PRAGMA optimize');
    }

    public function checkpoint(): void
    {
        $this->pdo->exec('PRAGMA wal_checkpoint(PASSIVE)');
    }

    public function integrityCheck(): bool
    {
        $statement = $this->pdo->query('PRAGMA integrity_check');
        if ($statement === false) {
            return false;
        }

        $result = $statement->fetchColumn();

        return $result === 'ok';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stats(): array
    {
        $statement = $this->pdo->query(
            'SELECT name, SUM(pgsize) AS bytes, SUM(ncell) AS rows '
            . 'FROM dbstat '
            . "WHERE name NOT LIKE 'sqlite_%' "
            . 'GROUP BY name'
        );

        if ($statement === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $results */
        $results = $statement->fetchAll();

        return $results;
    }
}
