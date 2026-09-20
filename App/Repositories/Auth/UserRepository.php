<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use PDO;

final class UserRepository
{
    private const COLS_JOIN = 'SELECT u.*, r.name AS role_name, r.permissions AS role_permissions ';
    private const FROM_JOIN = 'FROM users u ';
    private const LEFT_JOIN = 'LEFT JOIN roles r ON u.role_id = r.id ';
    private const LIMIT_ONE = 'LIMIT 1';

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByUsername(string $username): ?array
    {
        $sql = self::COLS_JOIN
            . self::FROM_JOIN
            . self::LEFT_JOIN
            . 'WHERE u.username = :username '
            . self::LIMIT_ONE;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([':username' => $username]);
        $record = $statement->fetch();

        /** @var array<string, mixed>|null */
        return is_array($record) ? $record : null;
    }

    public function updateLastLogin(int $userId, string $ipAddress): void
    {
        $sql = 'UPDATE users '
            . 'SET last_login_at = :last_login_at, '
            . '    last_login_ip = :last_login_ip, '
            . '    failed_attempts = 0, '
            . '    locked_until = NULL '
            . 'WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':last_login_at' => date('Y-m-d H:i:s'),
            ':last_login_ip' => $ipAddress,
            ':id'            => $userId,
        ]);
    }

    public function incrementFailedAttempt(int $userId, int $maxAttempts, int $lockoutSeconds): void
    {
        $sql = 'UPDATE users '
            . 'SET failed_attempts = failed_attempts + 1, '
            . '    locked_until = CASE '
            . '        WHEN failed_attempts + 1 >= :max_attempts THEN :locked_until '
            . '        ELSE locked_until '
            . '    END '
            . 'WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':max_attempts' => $maxAttempts,
            ':locked_until' => date('Y-m-d H:i:s', time() + $lockoutSeconds),
            ':id'           => $userId,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $userId): ?array
    {
        $sql = self::COLS_JOIN
            . self::FROM_JOIN
            . self::LEFT_JOIN
            . 'WHERE u.id = :id '
            . self::LIMIT_ONE;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([':id' => $userId]);
        $record = $statement->fetch();

        /** @var array<string, mixed>|null */
        return is_array($record) ? $record : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        $sql = self::COLS_JOIN
            . self::FROM_JOIN
            . self::LEFT_JOIN
            . 'WHERE u.email = :email '
            . self::LIMIT_ONE;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([':email' => $email]);
        $record = $statement->fetch();

        /** @var array<string, mixed>|null */
        return is_array($record) ? $record : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $sql = 'SELECT u.id, u.role_id, u.username, u.email, u.last_login_at, '
            . '       u.is_active, u.created_at, r.name AS role_name '
            . self::FROM_JOIN
            . self::LEFT_JOIN
            . 'ORDER BY u.id ASC';

        $statement = $this->pdo->query($sql);

        if ($statement === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $sql = 'INSERT INTO users '
            . '(role_id, username, email, password_hash, is_active, created_at, updated_at) '
            . 'VALUES (:role_id, :username, :email, :password_hash, :is_active, '
            . 'CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)';

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':role_id'       => (int) ($data['role_id'] ?? 1),
            ':username'      => (string) ($data['username'] ?? ''),
            ':email'         => (string) ($data['email'] ?? ''),
            ':password_hash' => (string) ($data['password_hash'] ?? ''),
            ':is_active'     => (int) ($data['is_active'] ?? 1),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $userId, array $data): void
    {
        $fields = [];
        $params = [':id' => $userId];

        if (isset($data['role_id'])) {
            $fields[]           = 'role_id = :role_id';
            $params[':role_id'] = (int) $data['role_id'];
        }
        if (isset($data['username'])) {
            $fields[]            = 'username = :username';
            $params[':username'] = (string) $data['username'];
        }
        if (isset($data['email'])) {
            $fields[]         = 'email = :email';
            $params[':email'] = (string) $data['email'];
        }
        if (isset($data['is_active'])) {
            $fields[]             = 'is_active = :is_active';
            $params[':is_active'] = (int) $data['is_active'];
        }

        if ($fields === []) {
            return;
        }

        $fields[] = 'updated_at = CURRENT_TIMESTAMP';
        $sql      = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        $sql       = 'UPDATE users SET password_hash = :hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            ':hash' => $passwordHash,
            ':id'   => $userId,
        ]);
    }

    public function delete(int $userId): void
    {
        $sql       = 'DELETE FROM users WHERE id = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->execute([':id' => $userId]);
    }
}
