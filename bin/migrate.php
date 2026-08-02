<?php

declare(strict_types=1);

use App\Database\ConnectionFactory;
use App\Support\Config;
use App\Support\Env;
use App\Support\Path;

require_once dirname(__DIR__) . '/vendor/autoload.php';

Path::bootstrap(dirname(__DIR__));
Env::load(Path::base('.env'));

$config = new Config([
    'database' => require Path::config('database.php'),
]);

$factory = new ConnectionFactory($config);
$pdo = $factory->create();
$migrationsPath = Path::base('Database/Migrations');
$files = glob($migrationsPath . '/*.php');

if ($files === false) {
    fwrite(STDERR, "Unable to read migration directory.\n");
    exit(1);
}

sort($files);

foreach ($files as $file) {
    $queries = require $file;
    if (! is_array($queries)) {
        continue;
    }

    foreach ($queries as $query) {
        if (is_string($query) && trim($query) !== '') {
            $pdo->exec($query);
        }
    }
}

fwrite(STDOUT, "Migrations completed successfully.\n");
