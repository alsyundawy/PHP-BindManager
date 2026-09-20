<?php

declare(strict_types=1);

use App\Container\Container;
use App\Http\Router;
use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use App\Services\Dns\ZoneFileService;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

// Helper to parse request body as array whether JSON or form-encoded.
$parseBody = static function (ServerRequestInterface $req): array {
    $body = $req->getParsedBody();
    if (is_array($body) && $body !== []) {
        $result = $body;
    } else {
        $raw    = (string) $req->getBody();
        $result = [];
        if ($raw !== '') {
            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                $result  = is_array($decoded) ? $decoded : [];
            } catch (\JsonException) {
                $result = [];
            }
        }
    }

    return $result;
};

$zoneNotFound = ['error' => 'Zone not found.'];

return [
    // --- Health check ---
    [
        'method'     => 'GET',
        'path'       => '/api/v1/system/health',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zones */
            $zones = $container->get(ZoneRepository::class);

            $zoneCount = count($zones->all());
            $bind9Up   = isBind9Active();

            return Router::json([
                'status'    => 'ok',
                'service'   => 'PHP-BindManager API',
                'version'   => '1.0.0',
                'database'  => 'connected',
                'zones'     => $zoneCount,
                'bind9'     => $bind9Up ? 'active' : 'inactive',
                'timestamp' => time(),
            ]);
        },
    ],

    // --- Zones ---
    [
        'method'     => 'GET',
        'path'       => '/api/v1/zones',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $repo */
            $repo = $container->get(ZoneRepository::class);

            return Router::json([
                'zones' => $repo->all(),
                'total' => count($repo->all()),
            ]);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/api/v1/zones',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req) use ($parseBody): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $repo */
            $repo = $container->get(ZoneRepository::class);
            $body = $parseBody($req);

            $name     = trim((string) ($body['name'] ?? ''));
            $zoneType = (string) ($body['zone_type'] ?? 'master');
            $viewId   = isset($body['view_id']) && $body['view_id'] !== '' ? (int) $body['view_id'] : null;

            if ($name === '') {
                return Router::json(['error' => 'Zone name is required.'], 400);
            }

            $cleanName = ltrim(rtrim($name, '.'), '.');
            $filePath  = '/etc/bind/zones/db.' . $cleanName;

            $id = $repo->create([
                'name'      => $name,
                'zone_type' => $zoneType,
                'file_path' => $filePath,
                'view_id'   => $viewId,
            ]);

            return Router::json([
                'status' => 'created',
                'id'     => $id,
                'zone'   => $repo->find($id),
            ], 201);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/api/v1/zones/{id}',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req) use ($zoneNotFound): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);
            /** @var RecordRepository $recordRepo */
            $recordRepo = $container->get(RecordRepository::class);

            $id   = (int) $req->getAttribute('id');
            $zone = $zoneRepo->find($id);

            if ($zone === null) {
                return Router::json($zoneNotFound, 404);
            }

            $records = $recordRepo->forZone($id);

            return Router::json([
                'zone'    => $zone,
                'records' => $records,
            ]);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/api/v1/zones/{id}/deploy',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req) use ($zoneNotFound): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneFileService $service */
            $service = $container->get(ZoneFileService::class);
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);

            $id   = (int) $req->getAttribute('id');
            $zone = $zoneRepo->find($id);

            if ($zone === null) {
                return Router::json($zoneNotFound, 404);
            }

            $error = null;

            try {
                $service->deploy($id);
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }

            if ($error !== null) {
                return Router::json([
                    'error'   => 'Deployment failed',
                    'message' => $error,
                ], 500);
            }

            return Router::json([
                'status'  => 'deployed',
                'zone_id' => $id,
                'zone'    => $zoneRepo->find($id),
            ]);
        },
    ],
    [
        'method'     => 'DELETE',
        'path'       => '/api/v1/zones/{id}',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req) use ($zoneNotFound): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);

            $id   = (int) $req->getAttribute('id');
            $zone = $zoneRepo->find($id);

            if ($zone === null) {
                return Router::json($zoneNotFound, 404);
            }

            $zoneRepo->delete($id);

            return Router::json([
                'status'  => 'deleted',
                'zone_id' => $id,
            ]);
        },
    ],

    // --- Records ---
    [
        'method'     => 'GET',
        'path'       => '/api/v1/records',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var RecordRepository $repo */
            $repo   = $container->get(RecordRepository::class);
            $zoneId = (int) ($req->getQueryParams()['zone_id'] ?? 0);

            $records = $zoneId > 0 ? $repo->forZone($zoneId) : $repo->all();

            return Router::json([
                'records' => $records,
                'total'   => count($records),
                'zone_id' => $zoneId > 0 ? $zoneId : null,
            ]);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/api/v1/records',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req) use ($parseBody): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var RecordRepository $repo */
            $repo = $container->get(RecordRepository::class);
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);
            $body     = $parseBody($req);

            $zoneId   = (int) ($body['zone_id'] ?? 0);
            $name     = trim((string) ($body['name'] ?? ''));
            $type     = strtoupper(trim((string) ($body['record_type'] ?? 'A')));
            $ttl      = max(1, (int) ($body['ttl'] ?? 3600));
            $priority = isset($body['priority']) && $body['priority'] !== '' ? (int) $body['priority'] : null;
            $content  = trim((string) ($body['content'] ?? ''));

            if ($zoneId <= 0 || $zoneRepo->find($zoneId) === null) {
                return Router::json(['error' => 'Valid zone_id is required.'], 400);
            }

            if ($name === '' || $content === '') {
                return Router::json(['error' => 'Record name and content are required.'], 400);
            }

            $id = $repo->create($zoneId, [
                'name'        => $name,
                'record_type' => $type,
                'ttl'         => $ttl,
                'priority'    => $priority,
                'content'     => $content,
            ]);

            return Router::json([
                'status' => 'created',
                'id'     => $id,
                'record' => $repo->find($id),
            ], 201);
        },
    ],
    [
        'method'     => 'DELETE',
        'path'       => '/api/v1/records/{id}',
        'auth'       => false,
        'csrf'       => false,
        'rate_limit' => 'api',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var RecordRepository $repo */
            $repo = $container->get(RecordRepository::class);

            $id     = (int) $req->getAttribute('id');
            $record = $repo->find($id);

            if ($record === null) {
                return Router::json(['error' => 'Record not found.'], 404);
            }

            $repo->delete($id);

            return Router::json([
                'status'    => 'deleted',
                'record_id' => $id,
            ]);
        },
    ],
];
