<?php

declare(strict_types=1);

namespace App\Repositories\System;

use PDO;

final class NotificationRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(?int $userId, string $severity, string $title, string $message): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO notifications(user_id, severity, title, message)
             VALUES(:user_id, :severity, :title, :message)'
        );
        $stmt->execute([
            ':user_id'  => $userId,
            ':severity' => $severity,
            ':title'    => $title,
            ':message'  => $message,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function unread(?int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notifications
             WHERE read_at IS NULL
               AND (user_id IS NULL OR user_id = :user_id)
             ORDER BY created_at DESC
             LIMIT 50'
        );
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }
}
