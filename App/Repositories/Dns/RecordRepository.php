<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

final class RecordRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM dns_records ORDER BY name, record_type');
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
        $statement = $this->pdo->prepare('SELECT * FROM dns_records WHERE id = :id');
        $statement->execute([':id' => $id]);
        $record = $statement->fetch();

        /** @var array<string, mixed>|null */
        return is_array($record) ? $record : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function forZone(int $zoneId): array
    {
        $sql       = 'SELECT * FROM dns_records WHERE zone_id = :zone_id ORDER BY name, record_type';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':zone_id' => $zoneId]);

        /** @var array<int, array<string, mixed>> */
        return $statement->fetchAll();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(int $zoneId, array $data): int
    {
        $sql = 'INSERT INTO dns_records (zone_id, name, record_type, ttl, priority, content) '
            . 'VALUES (:zone_id, :name, :record_type, :ttl, :priority, :content)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':zone_id'     => $zoneId,
            ':name'        => $data['name'],
            ':record_type' => $data['record_type'],
            ':ttl'         => $data['ttl'],
            ':priority'    => $data['priority'] ?? null,
            ':content'     => $data['content'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM dns_records WHERE id = :id');
        $statement->execute([':id' => $id]);
    }
}
