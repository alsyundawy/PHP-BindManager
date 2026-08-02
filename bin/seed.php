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

$roles = [
    ['name' => 'admin', 'description' => 'Full system administrator access', 'permissions' => json_encode(['*'], JSON_THROW_ON_ERROR)],
    ['name' => 'editor', 'description' => 'Zone and record management access', 'permissions' => json_encode(['dashboard.view', 'zones.*', 'records.*'], JSON_THROW_ON_ERROR)],
    ['name' => 'viewer', 'description' => 'Read-only access', 'permissions' => json_encode(['dashboard.view', 'zones.view', 'records.view', 'system.view'], JSON_THROW_ON_ERROR)],
];

$roleStatement = $pdo->prepare(
    'INSERT INTO roles (name, description, permissions) VALUES (:name, :description, :permissions) ON CONFLICT(name) DO UPDATE SET description = :description, permissions = :permissions'
);

foreach ($roles as $role) {
    $roleStatement->execute([
        ':name' => $role['name'],
        ':description' => $role['description'],
        ':permissions' => $role['permissions'],
    ]);
}

$roleId = (int) $pdo->query("SELECT id FROM roles WHERE name = 'admin' LIMIT 1")->fetchColumn();
$passwordHash = password_hash('ChangeMe@2026!', PASSWORD_ARGON2ID);

$userStatement = $pdo->prepare(
    'INSERT INTO users (role_id, username, email, password_hash) VALUES (:role_id, :username, :email, :password_hash) ON CONFLICT(username) DO NOTHING'
);
$userStatement->execute([
    ':role_id' => $roleId,
    ':username' => 'admin',
    ':email' => 'admin@localhost',
    ':password_hash' => $passwordHash,
]);

fwrite(STDOUT, "Database seeded successfully. Default admin: admin / ChangeMe@2026!\n");
