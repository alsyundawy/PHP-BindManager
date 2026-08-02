<?php

declare(strict_types=1);

namespace App\Services\System;

use PDO;

final class DatabaseOptimizer
{
    public function __construct(private readonly PDO $pdo){}
    /** @return array<string,mixed> */public function optimize():array{$before=(int)$this->pdo->query('PRAGMA page_count')->fetchColumn()*(int)$this->pdo->query('PRAGMA page_size')->fetchColumn();$this->pdo->exec('PRAGMA optimize');$this->pdo->exec('VACUUM');$this->pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');$after=(int)$this->pdo->query('PRAGMA page_count')->fetchColumn()*(int)$this->pdo->query('PRAGMA page_size')->fetchColumn();return ['bytes_before'=>$before,'bytes_after'=>$after,'bytes_reclaimed'=>max(0,$before-$after)];}
}
