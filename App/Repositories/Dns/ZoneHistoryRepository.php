<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

/**
 * Manages zone revision history and rollbacks.
 */
final class ZoneHistoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function getHistoryForZone(int $zoneId): array
    {
        $sql = 'SELECT h.*, u.username FROM zone_history h '
            . 'LEFT JOIN users u ON u.id = h.created_by '
            . 'WHERE h.zone_id = :zone_id '
            . 'ORDER BY h.id DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':zone_id' => $zoneId]);

        /** @var array<int, array<string, mixed>> */
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @return array<string, mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM zone_history WHERE id = :id');
        $stmt->execute([':id' => $id]);

        /** @var array<string, mixed>|false $row */
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : $row;
    }

    public function recordSnapshot(
        int $zoneId,
        int $serial,
        string $zoneContent,
        ?string $changeSummary,
        ?int $userId
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO zone_history (zone_id, serial, zone_content, change_summary, created_by) '
            . 'VALUES (:zone_id, :serial, :zone_content, :change_summary, :created_by)'
        );

        $stmt->execute([
            ':zone_id'        => $zoneId,
            ':serial'         => $serial,
            ':zone_content'   => $zoneContent,
            ':change_summary' => $changeSummary,
            ':created_by'     => $userId,
        ]);
    }
}
