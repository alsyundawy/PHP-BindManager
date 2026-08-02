<?php

declare(strict_types=1);

namespace App\Repositories\System;

use PDO;

final class AuditLogRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function write(
        ?int $userId,
        string $action,
        string $entityType,
        ?string $entityId,
        string $ip,
        string $agent,
        ?string $old = null,
        ?string $new = null,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs(user_id, action, entity_type, entity_id, old_value, new_value, ip_address, user_agent)
             VALUES(:user_id, :action, :entity_type, :entity_id, :old_value, :new_value, :ip_address, :user_agent)'
        );
        $stmt->execute([
            ':user_id'     => $userId,
            ':action'      => $action,
            ':entity_type' => $entityType,
            ':entity_id'   => $entityId,
            ':old_value'   => $old,
            ':new_value'   => $new,
            ':ip_address'  => $ip,
            ':user_agent'  => $agent,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recent(int $limit = 50): array
    {
        $limit = max(1, min($limit, 500));

        $stmt = $this->pdo->prepare(
            'SELECT * FROM audit_logs ORDER BY id DESC LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
