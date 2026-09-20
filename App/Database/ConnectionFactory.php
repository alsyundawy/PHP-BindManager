<?php

declare(strict_types=1);

namespace App\Database;

use App\Exceptions\DatabaseConnectionException;
use App\Support\Config;
use App\Support\Path;
use PDO;
use PDOException;

final class ConnectionFactory
{
    public function __construct(private readonly Config $config)
    {
    }

    public function create(): PDO
    {
        $path = (string) $this->config->get('database.path');

        if ($path !== ':memory:') {
            if ($path === '') {
                $path = Path::base('Database/bindmanager.sqlite');
            } elseif (! str_starts_with($path, '/')) {
                $path = Path::base($path);
            }

            $dir = dirname($path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0o775, true);
            }
        }

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
            throw new DatabaseConnectionException('Unable to establish database connection.', 0, $exception);
        }
    }
}
