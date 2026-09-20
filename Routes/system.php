<?php

declare(strict_types=1);

use App\Container\Container;
use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use App\Support\Config;
use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$htmlHeaders = ['Content-Type' => 'text/html; charset=UTF-8'];

return [
    [
        'method'     => 'GET',
        'path'       => '/system',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $container->get(ZoneRepository::class);
            /** @var RecordRepository $recordRepo */
            $recordRepo = $container->get(RecordRepository::class);
            /** @var Config $config */
            $config = $container->get(Config::class);

            $zones   = $zoneRepo->all();
            $records = $recordRepo->all();

            $dbPath = (string) $config->get('database.connections.sqlite.database', '');
            $dbSize = '0 KB';
            if ($dbPath !== '' && file_exists($dbPath)) {
                $bytes  = (int) filesize($dbPath);
                $dbSize = $bytes > 1048576
                    ? number_format($bytes / 1048576, 2) . ' MB'
                    : number_format($bytes / 1024, 1) . ' KB';
            }

            $bind9Healthy = isBind9Active();

            $html = View::render('system/index', [
                'zoneCount'    => count($zones),
                'recordCount'  => count($records),
                'dbSize'       => $dbSize,
                'bind9Healthy' => $bind9Healthy,
                'phpVersion'   => PHP_VERSION,
                'zonesDir'     => (string) ($config->get('bind9.zones_dir') ?? '/etc/bind/zones'),
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'GET',
        'path'       => '/api/docs',
        'auth'       => false,
        'rate_limit' => 'web',
        'handler'    => static fn (): Response => new Response(
            200,
            $htmlHeaders,
            View::render('system/api-docs')
        ),
    ],
];
