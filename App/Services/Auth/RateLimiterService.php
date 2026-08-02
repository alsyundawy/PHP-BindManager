<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Repositories\Auth\LoginAttemptRepository;
use App\Support\Config;

final class RateLimiterService
{
    public function __construct(
        private readonly LoginAttemptRepository $repository,
        private readonly Config $config,
    ) {
    }

    public function allow(string $action, string $identifier): bool
    {
        $this->repository->clearExpired(time());
        $limit = $this->limitForAction($action);
        $record = $this->repository->find($identifier, $action);

        if ($record === null) {
            return true;
        }

        return (int) ($record['attempts'] ?? 0) < $limit;
    }

    public function hit(string $action, string $identifier): void
    {
        $resetAt = time() + 60;
        $this->repository->increment($identifier, $action, $resetAt);
    }

    public function clear(string $action, string $identifier): void
    {
        $this->repository->reset($identifier, $action);
    }

    private function limitForAction(string $action): int
    {
        return match ($action) {
            'login' => (int) $this->config->get('security.rate_limit_login', 10),
            'api' => (int) $this->config->get('api.rate_limit', 300),
            default => 120,
        };
    }
}
