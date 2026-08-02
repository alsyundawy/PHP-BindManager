<?php

declare(strict_types=1);

namespace App\Controllers\System;

use App\Services\System\DatabaseOptimizer;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

final class SystemController
{
    public function __construct(private readonly DatabaseOptimizer $optimizer)
    {
    }

    public function health(ServerRequestInterface $request): Response
    {
        $payload = [
            'success' => true,
            'data'    => [
                'database'  => $this->optimizer->optimize(),
                'timestamp' => gmdate(DATE_ATOM),
            ],
        ];

        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }
}
