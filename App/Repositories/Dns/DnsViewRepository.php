<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

/**
 * Manages BIND9 named view blocks stored in the `dns_views` table.
 */
final class DnsViewRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM dns_views ORDER BY name ASC');

        if ($stmt === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM dns_views WHERE id = :id');
        $stmt->execute([':id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed>|null */
    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM dns_views WHERE name = :name');
        $stmt->execute([':name' => $name]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed> */
    public function create(string $name, string $matchClients, ?string $description): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO dns_views(name, match_clients, description)
             VALUES(:name, :match_clients, :description)'
        );
        $stmt->execute([
            ':name'          => $name,
            ':match_clients' => $matchClients,
            ':description'   => $description,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [];
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM dns_views WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
