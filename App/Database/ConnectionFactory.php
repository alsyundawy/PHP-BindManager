<?php

declare(strict_types=1);

namespace App\Database;

use App\Support\Config;
use PDO;
use PDOException;
use RuntimeException;

final class ConnectionFactory
{
    public function __construct(private readonly Config $config)
    {
    }

    public function create(): PDO
    {
        $path = (string) $this->config->get('database.path');

        try {
            $pdo = new PDO('sqlite:' . $path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->exec('PRAGMA journal_mode = WAL;');
            $pdo->exec('PRAGMA foreign_keys = ON;');
            $pdo->exec('PRAGMA busy_timeout = 5000;');

            return $pdo;
        } catch (PDOException $exception) {
            throw new RuntimeException('Unable to establish database connection.', 0, $exception);
        }
    }
}
