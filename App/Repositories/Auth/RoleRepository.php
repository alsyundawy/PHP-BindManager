<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use PDO;

final class RoleRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM roles ORDER BY id ASC');

        if ($statement === false) {
            return [];
        }

        /** @var array<int, array<string, mixed>> */
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByName(string $name): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM roles WHERE name = :name LIMIT 1');
        $statement->execute([':name' => $name]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }
}
