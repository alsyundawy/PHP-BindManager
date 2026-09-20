<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Repositories\Auth\UserRepository;
use PDO;
use PHPUnit\Framework\TestCase;

final class UserRepositoryTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE roles (id INTEGER PRIMARY KEY, name TEXT, permissions TEXT)');
        $this->pdo->exec('CREATE TABLE users (
            id INTEGER PRIMARY KEY,
            role_id INTEGER,
            username TEXT,
            email TEXT,
            password_hash TEXT,
            last_login_at TEXT,
            last_login_ip TEXT,
            failed_attempts INTEGER DEFAULT 0,
            locked_until TEXT
        )');

        $this->pdo->exec("INSERT INTO roles (id, name, permissions) VALUES (1, 'admin', '[\"*\"]')");
        $this->pdo->exec("INSERT INTO users (id, role_id, username, email, password_hash)
            VALUES (1, 1, 'admin', 'admin@example.com', 'secret')");
    }

    public function testFindByUsernameJoinsRolesTableForRoleName(): void
    {
        $repo = new UserRepository($this->pdo);
        $user = $repo->findByUsername('admin');

        self::assertNotNull($user);
        self::assertSame('admin', $user['username']);
        self::assertSame('admin', $user['role_name']);
        self::assertSame('["*"]', $user['role_permissions']);
    }

    public function testFindByUsernameReturnsNullForNonExistentUser(): void
    {
        $repo = new UserRepository($this->pdo);
        $user = $repo->findByUsername('nonexistent');

        self::assertNull($user);
    }
}
