<?php

declare(strict_types=1);

use App\Container\Container;
use App\Exceptions\HttpException;
use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use App\Services\Dns\ZoneFileService;
use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$htmlHeaders = ['Content-Type' => 'text/html; charset=UTF-8'];
$pathZones   = '/zones';
$pathRecords = '/records';

return [
    // --- Zones ---
    [
        'method'     => 'GET',
        'path'       => $pathZones,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);
            /** @var RecordRepository $recordRepo */
            $recordRepo = $container->get(RecordRepository::class);

            $zones  = $zoneRepo->all();
            $counts = [];
            foreach ($zones as $zone) {
                $zid          = (int) ($zone['id'] ?? 0);
                $counts[$zid] = count($recordRepo->forZone($zid));
            }

            $csrfToken = (string) ($_SESSION['_csrf']['value'] ?? '');
            $html      = View::render('zones/index', [
                'zones'        => $zones,
                'recordCounts' => $counts,
                'csrfToken'    => $csrfToken,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/zones/create',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function () use ($htmlHeaders): Response {
            $csrfToken  = (string) ($_SESSION['_csrf']['value'] ?? '');
            $flashError = '';
            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }

            $html = View::render('zones/create', [
                'csrfToken'  => $csrfToken,
                'flashError' => $flashError,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathZones,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $repo */
            $repo = $container->get(ZoneRepository::class);
            $body = (array) ($req->getParsedBody() ?? []);

            $name      = trim((string) ($body['name'] ?? ''));
            $zoneType  = (string) ($body['zone_type'] ?? 'master');
            $viewId    = isset($body['view_id']) && $body['view_id'] !== '' ? (int) $body['view_id'] : null;
            $cleanName = ltrim(rtrim($name, '.'), '.');
            $filePath  = '/etc/bind/zones/db.' . $cleanName;

            if ($name === '') {
                $_SESSION['flash_error'] = 'Zone name is required.';

                return new Response(302, ['Location' => '/zones/create']);
            }

            $id = $repo->create([
                'name'      => $name,
                'zone_type' => $zoneType,
                'file_path' => $filePath,
                'view_id'   => $viewId,
            ]);

            $_SESSION['flash_success'] = "Zone '{$name}' created successfully.";

            return new Response(302, ['Location' => "/zones/{$id}"]);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/zones/{id}',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);
            /** @var RecordRepository $recordRepo */
            $recordRepo = $container->get(RecordRepository::class);
            /** @var ZoneFileService $zoneService */
            $zoneService = $container->get(ZoneFileService::class);

            $id   = (int) $req->getAttribute('id');
            $zone = $zoneRepo->find($id);

            if ($zone === null) {
                throw new HttpException('Zone not found', 404);
            }

            $records = $recordRepo->forZone($id);

            try {
                $exportText = $zoneService->export($id);
            } catch (\Throwable) {
                $exportText = '; No records available to export';
            }

            $csrfToken = (string) ($_SESSION['_csrf']['value'] ?? '');
            $html      = View::render('zones/show', [
                'zone'       => $zone,
                'records'    => $records,
                'exportText' => $exportText,
                'csrfToken'  => $csrfToken,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/zones/{id}/deploy',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneFileService $service */
            $service = $container->get(ZoneFileService::class);
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);

            $id   = (int) $req->getAttribute('id');
            $zone = $zoneRepo->find($id);

            if ($zone === null) {
                throw new HttpException('Zone not found', 404);
            }

            try {
                $service->deploy($id);
                $_SESSION['flash_success'] = "Zone '{$zone['name']}' deployed successfully.";
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Deployment failed: ' . $e->getMessage();
            }

            return new Response(302, ['Location' => "/zones/{$id}"]);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/zones/{id}/delete',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($pathZones): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);

            $id   = (int) $req->getAttribute('id');
            $zone = $zoneRepo->find($id);

            if ($zone !== null) {
                $zoneRepo->delete($id);
                $_SESSION['flash_success'] = "Zone '{$zone['name']}' deleted.";
            }

            return new Response(302, ['Location' => $pathZones]);
        },
    ],

    // --- Records ---
    [
        'method'     => 'GET',
        'path'       => $pathRecords,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var RecordRepository $recordRepo */
            $recordRepo = $container->get(RecordRepository::class);
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);

            $zoneId    = (int) ($req->getQueryParams()['zone_id'] ?? 0);
            $zones     = $zoneRepo->all();
            $records   = $zoneId > 0 ? $recordRepo->forZone($zoneId) : $recordRepo->all();
            $csrfToken = (string) ($_SESSION['_csrf']['value'] ?? '');

            $html = View::render('records/index', [
                'records'   => $records,
                'zones'     => $zones,
                'zoneId'    => $zoneId,
                'csrfToken' => $csrfToken,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/records/create',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);

            $zones      = $zoneRepo->all();
            $selectedId = (int) ($req->getQueryParams()['zone_id'] ?? 0);
            $csrfToken  = (string) ($_SESSION['_csrf']['value'] ?? '');

            $html = View::render('records/create', [
                'zones'      => $zones,
                'selectedId' => $selectedId,
                'csrfToken'  => $csrfToken,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathRecords,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var RecordRepository $recordRepo */
            $recordRepo = $container->get(RecordRepository::class);
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);
            $body     = (array) ($req->getParsedBody() ?? []);

            $zoneId   = (int) ($body['zone_id'] ?? 0);
            $name     = trim((string) ($body['name'] ?? ''));
            $type     = strtoupper(trim((string) ($body['record_type'] ?? 'A')));
            $ttl      = max(1, (int) ($body['ttl'] ?? 3600));
            $priority = isset($body['priority']) && $body['priority'] !== '' ? (int) $body['priority'] : null;
            $content  = trim((string) ($body['content'] ?? ''));

            if ($zoneId <= 0 || $zoneRepo->find($zoneId) === null) {
                $_SESSION['flash_error'] = 'A valid DNS zone must be selected.';

                return new Response(302, ['Location' => '/records/create']);
            }

            if ($name === '' || $content === '') {
                $_SESSION['flash_error'] = 'Record name and content cannot be empty.';

                return new Response(302, ['Location' => "/records/create?zone_id={$zoneId}"]);
            }

            $recordRepo->create($zoneId, [
                'name'        => $name,
                'record_type' => $type,
                'ttl'         => $ttl,
                'priority'    => $priority,
                'content'     => $content,
            ]);

            $_SESSION['flash_success'] = "Record '{$name}' ({$type}) created successfully.";

            return new Response(302, ['Location' => "/records?zone_id={$zoneId}"]);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => '/records/{id}/delete',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($pathRecords): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var RecordRepository $recordRepo */
            $recordRepo = $container->get(RecordRepository::class);

            $id     = (int) $req->getAttribute('id');
            $zoneId = (int) ($req->getQueryParams()['zone_id'] ?? 0);
            $record = $recordRepo->find($id);

            if ($record !== null) {
                if ($zoneId === 0 && isset($record['zone_id'])) {
                    $zoneId = (int) $record['zone_id'];
                }
                $recordRepo->delete($id);
                $_SESSION['flash_success'] = "Record '{$record['name']}' deleted.";
            }

            $redirectUri = $zoneId > 0 ? "{$pathRecords}?zone_id={$zoneId}" : $pathRecords;

            return new Response(302, ['Location' => $redirectUri]);
        },
    ],
];
