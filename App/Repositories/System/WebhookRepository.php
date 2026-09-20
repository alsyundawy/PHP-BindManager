<?php

declare(strict_types=1);

namespace App\Repositories\System;

use PDO;

/**
 * Manages webhook dispatch registrations.
 */
final class WebhookRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM webhooks ORDER BY id DESC');

        if ($stmt === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM webhooks WHERE id = :id');
        $stmt->execute([':id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed> */
    public function create(string $name, string $url, ?string $secret, string $events): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO webhooks (name, url, secret, events) VALUES (:name, :url, :secret, :events)'
        );
        $stmt->execute([
            ':name'   => $name,
            ':url'    => $url,
            ':secret' => $secret,
            ':events' => $events,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [];
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM webhooks WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function updateStatus(int $id, int $statusCode): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE webhooks SET last_status = :status, last_triggered_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            ':status' => $statusCode,
            ':id'     => $id,
        ]);
    }
}
