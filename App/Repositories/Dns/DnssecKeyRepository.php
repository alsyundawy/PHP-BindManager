<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

/**
 * Manages DNSSEC keys stored in the `dnssec_keys` table.
 */
final class DnssecKeyRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT dk.*, z.name AS zone_name
             FROM dnssec_keys dk
             LEFT JOIN zones z ON z.id = dk.zone_id
             ORDER BY dk.created_at DESC'
        );

        if ($stmt === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<int, array<string, mixed>> */
    public function forZone(int $zoneId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM dnssec_keys WHERE zone_id = :zone_id ORDER BY created_at DESC'
        );
        $stmt->execute([':zone_id' => $zoneId]);

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM dnssec_keys WHERE id = :id');
        $stmt->execute([':id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    /** @return array<string, mixed> */
    public function create(
        int $zoneId,
        string $keyRole,
        int $keyTag,
        int $algorithm,
        string $keyFile,
        ?string $publicKey,
    ): array {
        $stmt = $this->pdo->prepare(
            'INSERT INTO dnssec_keys(zone_id, key_role, key_tag, algorithm, key_file, public_key)
             VALUES(:zone_id, :key_role, :key_tag, :algorithm, :key_file, :public_key)'
        );
        $stmt->execute([
            ':zone_id'    => $zoneId,
            ':key_role'   => $keyRole,
            ':key_tag'    => $keyTag,
            ':algorithm'  => $algorithm,
            ':key_file'   => $keyFile,
            ':public_key' => $publicKey,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [];
    }

    public function retire(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE dnssec_keys SET status = 'retired' WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    public function revoke(int $id): void
    {
        $stmt = $this->pdo->prepare(
            "UPDATE dnssec_keys SET status = 'revoked' WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }
}
