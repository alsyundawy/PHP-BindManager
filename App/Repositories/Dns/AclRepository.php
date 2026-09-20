<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

/**
 * Manages BIND9 named ACL entries stored in the `acls` table.
 */
final class AclRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM acls ORDER BY name ASC');

        if ($stmt === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM acls WHERE id = :id');
        $stmt->execute([':id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM acls WHERE name = :name');
        $stmt->execute([':name' => $name]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed> */
    public function create(string $name, string $entries, ?string $description): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO acls(name, entries, description) VALUES(:name, :entries, :description)'
        );
        $stmt->execute([
            ':name'        => $name,
            ':entries'     => $entries,
            ':description' => $description,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [];
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE acls SET name = :name, entries = :entries,
             description = :description, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );
        $stmt->execute([
            ':name'        => (string) ($data['name'] ?? ''),
            ':entries'     => (string) ($data['entries'] ?? ''),
            ':description' => isset($data['description']) ? (string) $data['description'] : null,
            ':id'          => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM acls WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
