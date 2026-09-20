<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

/**
 * Manages reusable zone templates.
 */
final class ZoneTemplateRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM zone_templates ORDER BY name ASC');

        if ($stmt === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM zone_templates WHERE id = :id');
        $stmt->execute([':id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed> */
    public function create(string $name, ?string $description, string $recordsJson): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO zone_templates (name, description, records_json) VALUES (:name, :description, :records_json)'
        );
        $stmt->execute([
            ':name'         => $name,
            ':description'  => $description,
            ':records_json' => $recordsJson,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [];
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM zone_templates WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
