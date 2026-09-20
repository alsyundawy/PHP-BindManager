<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Exceptions\HttpException;
use App\Http\Router;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class RouterParameterTest extends TestCase
{
    public function testMatchesExactPath(): void
    {
        $routes = [
            [
                'method'  => 'GET',
                'path'    => '/zones',
                'handler' => static fn (): Response => new Response(200, [], 'zones list'),
            ],
        ];

        $router  = Router::fromRoutes($routes);
        $request = new ServerRequest('GET', '/zones');
        $match   = $router->match($request);

        self::assertSame('zones list', (string) $match->dispatch($match->request)->getBody());
    }

    public function testMatchesSingleParameterPath(): void
    {
        $routes = [
            [
                'method'  => 'GET',
                'path'    => '/zones/{id}',
                'handler' => static function (ServerRequestInterface $req): Response {
                    $id = (string) $req->getAttribute('id');

                    return new Response(200, [], "zone: {$id}");
                },
            ],
        ];

        $router  = Router::fromRoutes($routes);
        $request = new ServerRequest('GET', '/zones/42');
        $match   = $router->match($request);

        self::assertSame('42', $match->request->getAttribute('id'));
        self::assertSame(['id' => '42'], $match->request->getAttribute('params'));
        self::assertSame('zone: 42', (string) $match->dispatch($match->request)->getBody());
    }

    public function testMatchesNestedActionParameterizedPath(): void
    {
        $routes = [
            [
                'method'  => 'POST',
                'path'    => '/zones/{id}/deploy',
                'handler' => static function (ServerRequestInterface $req): Response {
                    return new Response(200, [], 'deployed ' . (string) $req->getAttribute('id'));
                },
            ],
        ];

        $router  = Router::fromRoutes($routes);
        $request = new ServerRequest('POST', '/zones/100/deploy');
        $match   = $router->match($request);

        self::assertSame('100', $match->request->getAttribute('id'));
        self::assertSame('deployed 100', (string) $match->dispatch($match->request)->getBody());
    }

    public function testMatchesMultipleParameters(): void
    {
        $routes = [
            [
                'method'  => 'DELETE',
                'path'    => '/zones/{zone_id}/records/{record_id}',
                'handler' => static function (ServerRequestInterface $req): Response {
                    $zoneId   = (string) $req->getAttribute('zone_id');
                    $recordId = (string) $req->getAttribute('record_id');

                    return new Response(200, [], "deleted {$zoneId}/{$recordId}");
                },
            ],
        ];

        $router  = Router::fromRoutes($routes);
        $request = new ServerRequest('DELETE', '/zones/5/records/99');
        $match   = $router->match($request);

        self::assertSame('5', $match->request->getAttribute('zone_id'));
        self::assertSame('99', $match->request->getAttribute('record_id'));
        self::assertSame('deleted 5/99', (string) $match->dispatch($match->request)->getBody());
    }

    public function testThrowsNotFoundForNonMatchingParameterizedPath(): void
    {
        $routes = [
            [
                'method'  => 'GET',
                'path'    => '/zones/{id}',
                'handler' => static fn (): Response => new Response(200),
            ],
        ];

        $router  = Router::fromRoutes($routes);
        $request = new ServerRequest('GET', '/zones/42/extra/segments');

        $this->expectException(HttpException::class);
        $this->expectExceptionCode(404);

        $router->match($request);
    }

    public function testJsonHelperProducesJsonResponse(): void
    {
        $response = Router::json(['status' => 'healthy', 'count' => 3], 201);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame('{"status":"healthy","count":3}', (string) $response->getBody());
    }
}
