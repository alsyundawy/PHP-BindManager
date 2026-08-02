<?php

declare(strict_types=1);

namespace App\Services\System;

use PDO;

final class ApiTokenService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Issue a new API token.
     *
     * @param  array<string>  $scopes
     * @return array{token: string, id: int}
     */
    public function issue(
        int $userId,
        string $name,
        array $scopes,
        ?string $expiresAt = null,
    ): array {
        $plain = 'pbm_' . bin2hex(random_bytes(32));
        $hash  = hash('sha256', $plain); // NOSONAR — high-entropy random token

        $stmt = $this->pdo->prepare(
            'INSERT INTO api_tokens(user_id, name, token_hash, scopes, expires_at)
             VALUES(:user_id, :name, :token_hash, :scopes, :expires_at)'
        );
        $stmt->execute([
            ':user_id'    => $userId,
            ':name'       => $name,
            ':token_hash' => $hash,
            ':scopes'     => json_encode(array_values($scopes), JSON_THROW_ON_ERROR),
            ':expires_at' => $expiresAt,
        ]);

        return [
            'token' => $plain,
            'id'    => (int) $this->pdo->lastInsertId(),
        ];
    }

    /**
     * Authenticate a plain token.
     *
     * @return array<string, mixed>|null
     */
    public function authenticate(string $plain): ?array
    {
        $hash = hash('sha256', $plain); // NOSONAR — high-entropy random token

        $stmt = $this->pdo->prepare(
            'SELECT * FROM api_tokens
             WHERE token_hash = :token_hash
               AND revoked_at IS NULL
               AND (expires_at IS NULL OR expires_at > CURRENT_TIMESTAMP)
             LIMIT 1'
        );
        $stmt->execute([':token_hash' => $hash]);

        $row = $stmt->fetch();

        if (! is_array($row)) {
            return null;
        }

        $update = $this->pdo->prepare(
            'UPDATE api_tokens SET last_used_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $update->execute([':id' => $row['id']]);

        return $row;
    }
}
