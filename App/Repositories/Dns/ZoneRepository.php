<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

final class ZoneRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM zones ORDER BY name');
        if ($statement === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM zones WHERE id = :id');
        $statement->execute([':id' => $id]);
        $record = $statement->fetch();

        /** @var array<string, mixed>|null */
        return is_array($record) ? $record : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO zones (name, zone_type, file_path, view_id, status) '
            . 'VALUES (:name, :zone_type, :file_path, :view_id, :status)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':name'      => $data['name'],
            ':zone_type' => $data['zone_type'],
            ':file_path' => $data['file_path'],
            ':view_id'   => $data['view_id'] ?? null,
            ':status'    => 'draft',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, string $status): void
    {
        $sql = 'UPDATE zones '
            . 'SET status = :status, updated_at = CURRENT_TIMESTAMP '
            . 'WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':status' => $status,
            ':id'     => $id,
        ]);
    }
}
