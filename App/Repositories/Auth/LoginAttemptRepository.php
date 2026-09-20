<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use PDO;

final class LoginAttemptRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function increment(string $identifier, string $action, int $resetAt): void
    {
        $sql = <<<'SQL'
                INSERT INTO rate_limits (identifier, action, attempts, reset_at)
                VALUES (:identifier, :action, 1, :reset_at)
                ON CONFLICT(identifier, action)
                DO UPDATE SET attempts = attempts + 1, reset_at = :reset_at
            SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':identifier' => $identifier,
            ':action'     => $action,
            ':reset_at'   => $resetAt,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $identifier, string $action): ?array
    {
        $sql = 'SELECT identifier, action, attempts, reset_at '
            . 'FROM rate_limits '
            . 'WHERE identifier = :identifier AND action = :action '
            . 'LIMIT 1';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':identifier' => $identifier,
            ':action'     => $action,
        ]);

        $record = $statement->fetch();

        /** @var array<string, mixed>|null */
        return is_array($record) ? $record : null;
    }

    public function clearExpired(int $timestamp): void
    {
        $statement = $this->pdo->prepare('DELETE FROM rate_limits WHERE reset_at < :timestamp');
        $statement->execute([':timestamp' => $timestamp]);
    }

    public function reset(string $identifier, string $action): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM rate_limits WHERE identifier = :identifier AND action = :action'
        );
        $statement->execute([
            ':identifier' => $identifier,
            ':action'     => $action,
        ]);
    }
}
