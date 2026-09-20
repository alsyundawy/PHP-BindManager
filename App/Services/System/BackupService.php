<?php

declare(strict_types=1);

namespace App\Services\System;

use PDO;
use RuntimeException;

final class BackupService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $backupDirectory,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function database(string $sourceName = 'bindmanager'): array
    {
        $this->ensureDirectory();
        $filename = $sourceName . '-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.sqlite';
        $path     = $this->safePath($filename);
        $quoted   = str_replace("'", "''", $path);

        $this->pdo->exec("VACUUM INTO '{$quoted}'");

        if (! is_file($path)) {
            throw new RuntimeException('Database backup was not created.');
        }

        return $this->metadata($path, 'database', $sourceName);
    }

    public function restore(string $backupPath, string $databasePath): void
    {
        if (! is_file($backupPath) || ! is_readable($backupPath)) {
            throw new RuntimeException('Backup file is unavailable.');
        }

        $realBackup      = realpath($backupPath);
        $realDatabaseDir = realpath(dirname($databasePath));
        $realBackupDir   = realpath($this->backupDirectory);

        if (
            $realBackup         === false
            || $realDatabaseDir === false
            || $realBackupDir   === false
            || ! str_starts_with($realBackup, $realBackupDir . DIRECTORY_SEPARATOR)
        ) {
            throw new RuntimeException('Backup path is outside the backup directory.');
        }

        $tmp = $databasePath . '.restore-' . bin2hex(random_bytes(6));
        if (! copy($realBackup, $tmp)) {
            throw new RuntimeException('Unable to stage database restore.');
        }

        if (! rename($tmp, $databasePath)) {
            @unlink($tmp);

            throw new RuntimeException('Unable to atomically replace database.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function metadata(string $path, string $type, string $source): array
    {
        $hash = hash_file('sha256', $path);
        $size = filesize($path);

        return [
            'backup_type' => $type,
            'source_name' => $source,
            'file_path'   => $path,
            'sha256'      => is_string($hash) ? $hash : '',
            'size_bytes'  => is_int($size) ? $size : 0,
        ];
    }

    private function ensureDirectory(): void
    {
        if (
            ! is_dir($this->backupDirectory)
            && ! mkdir($this->backupDirectory, 0o750, true)
            && ! is_dir($this->backupDirectory)
        ) {
            throw new RuntimeException('Unable to create backup directory.');
        }
    }

    private function safePath(string $filename): string
    {
        $base = realpath($this->backupDirectory);
        if ($base === false) {
            throw new RuntimeException('Backup directory is invalid.');
        }

        return $base . DIRECTORY_SEPARATOR . $filename;
    }
}
