<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use PDO;

final class SessionRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function store(string $sessionId, int $userId, string $ipAddress, string $userAgent, int $lastActivityAt): void
    {
        $sql = <<<'SQL'
            INSERT INTO user_sessions (session_id, user_id, ip_address, user_agent, last_activity_at)
            VALUES (:session_id, :user_id, :ip_address, :user_agent, :last_activity_at)
            ON CONFLICT(session_id)
            DO UPDATE SET last_activity_at = :last_activity_at, ip_address = :ip_address, user_agent = :user_agent
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':session_id' => $sessionId,
            ':user_id' => $userId,
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
            ':last_activity_at' => $lastActivityAt,
        ]);
    }

    public function delete(string $sessionId): void
    {
        $statement = $this->pdo->prepare('DELETE FROM user_sessions WHERE session_id = :session_id');
        $statement->execute([':session_id' => $sessionId]);
    }
}
