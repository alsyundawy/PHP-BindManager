<?php

declare(strict_types=1);

namespace App\Repositories\System;

use PDO;

final class ActivityLogRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function write(
        ?int $userId,
        string $category,
        string $action,
        string $message,
        array $context = [],
        ?string $ip = null,
        ?string $agent = null,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO activity_logs(user_id, category, action, message, context, ip_address, user_agent)
             VALUES(:user_id, :category, :action, :message, :context, :ip_address, :user_agent)'
        );
        $stmt->execute([
            ':user_id'    => $userId,
            ':category'   => $category,
            ':action'     => $action,
            ':message'    => $message,
            ':context'    => json_encode($context, JSON_THROW_ON_ERROR),
            ':ip_address' => $ip,
            ':user_agent' => $agent,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 100, ?string $category = null): array
    {
        $limit = max(1, min($limit, 1000));

        if ($category !== null && $category !== '') {
            $stmt = $this->pdo->prepare(
                'SELECT a.*, u.username
                 FROM activity_logs a
                 LEFT JOIN users u ON u.id = a.user_id
                 WHERE a.category = :category
                 ORDER BY a.id DESC LIMIT :limit'
            );
            $stmt->bindValue(':category', $category);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT a.*, u.username
                 FROM activity_logs a
                 LEFT JOIN users u ON u.id = a.user_id
                 ORDER BY a.id DESC LIMIT :limit'
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(): int
    {
        $row = $this->pdo->query('SELECT COUNT(*) FROM activity_logs')->fetch(PDO::FETCH_NUM);

        return is_array($row) ? (int) ($row[0] ?? 0) : 0;
    }
}
