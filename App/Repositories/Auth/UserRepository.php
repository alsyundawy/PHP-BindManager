<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $statement->execute([':username' => $username]);
        $record = $statement->fetch();

        return is_array($record) ? $record : null;
    }

    public function updateLastLogin(int $userId, string $ipAddress): void
    {
        $statement = $this->pdo->prepare('UPDATE users SET last_login_at = :last_login_at, last_login_ip = :last_login_ip, failed_attempts = 0, locked_until = NULL WHERE id = :id');
        $statement->execute([
            ':last_login_at' => date('Y-m-d H:i:s'),
            ':last_login_ip' => $ipAddress,
            ':id' => $userId,
        ]);
    }

    public function incrementFailedAttempt(int $userId, int $maxAttempts, int $lockoutSeconds): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET failed_attempts = failed_attempts + 1, locked_until = CASE WHEN failed_attempts + 1 >= :max_attempts THEN :locked_until ELSE locked_until END WHERE id = :id'
        );
        $statement->execute([
            ':max_attempts' => $maxAttempts,
            ':locked_until' => date('Y-m-d H:i:s', time() + $lockoutSeconds),
            ':id' => $userId,
        ]);
    }
}
