<?php

declare(strict_types=1);

namespace App\Repositories\System;

use PDO;

/**
 * Persists backup metadata in the `backups` table.
 * The actual file copy is performed by BackupService.
 */
final class BackupRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string, mixed> */
    public function create(
        string $backupType,
        string $sourceName,
        string $filePath,
        string $sha256,
        int $sizeBytes,
        ?int $createdBy,
    ): array {
        $stmt = $this->pdo->prepare(
            'INSERT INTO backups(backup_type, source_name, file_path, sha256, size_bytes, created_by)
             VALUES(:backup_type, :source_name, :file_path, :sha256, :size_bytes, :created_by)'
        );
        $stmt->execute([
            ':backup_type' => $backupType,
            ':source_name' => $sourceName,
            ':file_path'   => $filePath,
            ':sha256'      => $sha256,
            ':size_bytes'  => $sizeBytes,
            ':created_by'  => $createdBy,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [];
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM backups ORDER BY created_at DESC'
        );

        if ($stmt === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM backups WHERE id = :id');
        $stmt->execute([':id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM backups WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }
}
