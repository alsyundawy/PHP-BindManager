<?php

declare(strict_types=1);

use App\Container\Container;
use App\Repositories\Api\ApiTokenRepository;
use App\Repositories\Dns\AclRepository;
use App\Repositories\Dns\DnssecKeyRepository;
use App\Repositories\Dns\DnsViewRepository;
use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use App\Repositories\System\ActivityLogRepository;
use App\Repositories\System\AuditLogRepository;
use App\Repositories\System\BackupRepository;
use App\Repositories\System\WebhookRepository;
use App\Services\Dns\ZoneFileService;
use App\Services\System\BackupService;
use App\Support\Config;
use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$htmlHeaders  = ['Content-Type' => 'text/html; charset=UTF-8'];
$pathBackups  = '/system/backups';
$pathTokens   = '/system/tokens';
$pathAcls     = '/acls';
$pathViews    = '/views';
$pathDnssec   = '/dnssec';
$pathWebhooks = '/system/webhooks';
$errPrefix    = 'Error: ';

/**
 * Extract public key content and key tag from generated key files.
 *
 * @return array{0: string|null, 1: int|null}
 */
$extractKeyData = static function (string $zonesDir, string $keyFile): array {
    $pubFile = $zonesDir . '/' . $keyFile . '.key';
    if (! is_file($pubFile)) {
        return [null, null];
    }
    $content   = file_get_contents($pubFile);
    $publicKey = ($content !== false && $content !== '') ? $content : null;
    $matches   = [];
    $keyTag    = preg_match('/\+(\d{5})$/', $keyFile, $matches) === 1 ? (int) $matches[1] : null;

    return [$publicKey, $keyTag];
};

/**
 * Helper to run dnssec-keygen if available.
 *
 * @return array{keyFile: string, keyTag: int, publicKey: string|null}
 */
$generateDnssecKey = static function (
    int $zoneId,
    string $keyRole,
    int $algorithm,
    string $zonesDir
) use ($extractKeyData): array {
    $keyFile   = 'K' . $zoneId . '-' . $keyRole . '-' . time();
    $keyTag    = random_int(1, 65535);
    $publicKey = null;

    $dnssecKeygen = '/usr/sbin/dnssec-keygen';
    if (! is_executable($dnssecKeygen)) {
        return ['keyFile' => $keyFile, 'keyTag' => $keyTag, 'publicKey' => $publicKey];
    }

    $roleFlag = $keyRole === 'ksk' ? ' -f KSK' : '';
    $cmd      = escapeshellcmd($dnssecKeygen)
               . $roleFlag
               . ' -a ' . $algorithm
               . ' -n ZONE'
               . ' -K ' . escapeshellarg($zonesDir)
               . ' zone' . $zoneId;
    $output  = [];
    $retCode = 0;
    exec($cmd . ' 2>&1', $output, $retCode);
    if ($retCode === 0 && isset($output[0])) {
        $keyFile                       = $output[0];
        [$extractedPub, $extractedTag] = $extractKeyData($zonesDir, $keyFile);
        $publicKey                     = $extractedPub;
        if ($extractedTag !== null) {
            $keyTag = $extractedTag;
        }
    }

    return [
        'keyFile'   => $keyFile,
        'keyTag'    => $keyTag,
        'publicKey' => $publicKey,
    ];
};

return [
    // --- System Health / Overview ---
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
    // --- API Docs ---
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
    // --- Backups ---
    [
        'method'     => 'GET',
        'path'       => $pathBackups,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var BackupRepository $backupRepo */
            $backupRepo = $c->get(BackupRepository::class);
            /** @var Config $config */
            $config = $c->get(Config::class);

            $flashSuccess = '';
            $flashError   = '';
            if (isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])) {
                $flashSuccess = $_SESSION['flash_success'];
                unset($_SESSION['flash_success']);
            }
            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }

            $dbPath = (string) $config->get('database.connections.sqlite.database', '');

            $html = View::render('system/backups', [
                'backups'      => $backupRepo->all(),
                'csrfToken'    => (string) ($_SESSION['_csrf']['value'] ?? ''),
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
                'dbPath'       => $dbPath,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathBackups,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($pathBackups, $errPrefix): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var BackupService $backupService */
            $backupService = $c->get(BackupService::class);
            /** @var BackupRepository $backupRepo */
            $backupRepo = $c->get(BackupRepository::class);
            /** @var Config $config */
            $config = $c->get(Config::class);

            $body   = (array) ($req->getParsedBody() ?? []);
            $action = (string) ($body['_action'] ?? '');
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            try {
                if ($action === 'create') {
                    $meta = $backupService->database('bindmanager');
                    $backupRepo->create(
                        (string) ($meta['backup_type'] ?? 'database'),
                        (string) ($meta['source_name'] ?? 'bindmanager'),
                        (string) ($meta['file_path'] ?? ''),
                        (string) ($meta['sha256'] ?? ''),
                        (int)   ($meta['size_bytes'] ?? 0),
                        $userId > 0 ? $userId : null,
                    );
                    $_SESSION['flash_success'] = 'Database backup created successfully.';
                } elseif ($action === 'restore') {
                    $id     = (int) ($body['id'] ?? 0);
                    $backup = $backupRepo->findById($id);
                    if ($backup === null) {
                        $_SESSION['flash_error'] = 'Backup not found.';
                    } else {
                        $dbPath = (string) $config->get('database.connections.sqlite.database', '');
                        $backupService->restore((string) ($backup['file_path'] ?? ''), $dbPath);
                        $_SESSION['flash_success'] = 'Database restored successfully. A reload may be required.';
                    }
                } elseif ($action === 'delete') {
                    $id     = (int) ($body['id'] ?? 0);
                    $backup = $backupRepo->findById($id);
                    if ($backup !== null) {
                        $filePath = (string) ($backup['file_path'] ?? '');
                        if ($filePath !== '' && is_file($filePath)) {
                            @unlink($filePath);
                        }
                        $backupRepo->delete($id);
                    }
                    $_SESSION['flash_success'] = 'Backup deleted.';
                }
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = $errPrefix . $e->getMessage();
            }

            return new Response(302, ['Location' => $pathBackups]);
        },
    ],
    // --- Activity Log ---
    [
        'method'     => 'GET',
        'path'       => '/system/activity',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var ActivityLogRepository $logRepo */
            $logRepo  = $c->get(ActivityLogRepository::class);
            $params   = $req->getQueryParams();
            $category = (string) ($params['category'] ?? '');

            $logs  = $logRepo->recent(200, $category !== '' ? $category : null);
            $total = $logRepo->count();

            $html = View::render('system/activity', [
                'logs'     => $logs,
                'category' => $category,
                'total'    => $total,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    // --- Audit Trail ---
    [
        'method'     => 'GET',
        'path'       => '/system/audit-logs',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var AuditLogRepository $auditRepo */
            $auditRepo = $c->get(AuditLogRepository::class);

            $logs  = $auditRepo->recent(200);
            $total = count($logs);

            $html = View::render('system/audit-logs', [
                'logs'  => $logs,
                'total' => $total,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    // --- API Tokens ---
    [
        'method'     => 'GET',
        'path'       => $pathTokens,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var ApiTokenRepository $tokenRepo */
            $tokenRepo = $c->get(ApiTokenRepository::class);

            $userId  = (int) ($_SESSION['user_id'] ?? 0);
            $isAdmin = (($_SESSION['user_role'] ?? '') === 'admin');

            $flashSuccess = '';
            $flashError   = '';
            if (isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])) {
                $flashSuccess = $_SESSION['flash_success'];
                unset($_SESSION['flash_success']);
            }
            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }
            $newToken = '';
            if (isset($_SESSION['new_api_token']) && is_string($_SESSION['new_api_token'])) {
                $newToken = $_SESSION['new_api_token'];
                unset($_SESSION['new_api_token']);
            }

            $tokens = $isAdmin ? $tokenRepo->all() : $tokenRepo->forUser($userId);

            $html = View::render('system/tokens', [
                'tokens'       => $tokens,
                'csrfToken'    => (string) ($_SESSION['_csrf']['value'] ?? ''),
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
                'newToken'     => $newToken !== '' ? $newToken : null,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathTokens,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($pathTokens, $errPrefix): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var ApiTokenRepository $tokenRepo */
            $tokenRepo = $c->get(ApiTokenRepository::class);

            $body   = (array) ($req->getParsedBody() ?? []);
            $action = (string) ($body['_action'] ?? '');
            $userId = (int) ($_SESSION['user_id'] ?? 0);

            try {
                if ($action === 'create') {
                    $name       = trim((string) ($body['name'] ?? ''));
                    $rawExpires = trim((string) ($body['expires_at'] ?? ''));
                    $expiresAt  = $rawExpires !== '' ? $rawExpires : null;
                    $rawScopes  = isset($body['scopes']) && is_array($body['scopes'])
                        ? array_map('strval', $body['scopes'])
                        : [];

                    if ($name === '') {
                        $_SESSION['flash_error'] = 'Token name is required.';
                    } else {
                        $plainToken = bin2hex(random_bytes(32));
                        $hash       = hash('sha256', $plainToken);
                        $tokenRepo->create($userId, $name, $hash, $rawScopes, $expiresAt);
                        $_SESSION['new_api_token'] = $plainToken;
                        $_SESSION['flash_success'] = "Token '{$name}' created.";
                    }
                } elseif ($action === 'revoke') {
                    $id = (int) ($body['id'] ?? 0);
                    $tokenRepo->revoke($id);
                    $_SESSION['flash_success'] = 'Token revoked.';
                } elseif ($action === 'delete') {
                    $id = (int) ($body['id'] ?? 0);
                    $tokenRepo->delete($id);
                    $_SESSION['flash_success'] = 'Token deleted.';
                }
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = $errPrefix . $e->getMessage();
            }

            return new Response(302, ['Location' => $pathTokens]);
        },
    ],
    // --- ACL Management ---
    [
        'method'     => 'GET',
        'path'       => $pathAcls,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var AclRepository $aclRepo */
            $aclRepo = $c->get(AclRepository::class);

            $flashSuccess = '';
            $flashError   = '';
            if (isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])) {
                $flashSuccess = $_SESSION['flash_success'];
                unset($_SESSION['flash_success']);
            }
            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }

            $html = View::render('acls/index', [
                'acls'         => $aclRepo->all(),
                'csrfToken'    => (string) ($_SESSION['_csrf']['value'] ?? ''),
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathAcls,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($pathAcls, $errPrefix): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var AclRepository $aclRepo */
            $aclRepo = $c->get(AclRepository::class);

            $body   = (array) ($req->getParsedBody() ?? []);
            $action = (string) ($body['_action'] ?? '');

            try {
                if ($action === 'create') {
                    $name    = trim((string) ($body['name'] ?? ''));
                    $entries = trim((string) ($body['entries'] ?? ''));
                    $rawDesc = trim((string) ($body['description'] ?? ''));
                    $desc    = $rawDesc !== '' ? $rawDesc : null;

                    if ($name === '' || $entries === '') {
                        $_SESSION['flash_error'] = 'ACL name and entries are required.';
                    } elseif ($aclRepo->findByName($name) !== null) {
                        $_SESSION['flash_error'] = "ACL '{$name}' already exists.";
                    } else {
                        $aclRepo->create($name, $entries, $desc);
                        $_SESSION['flash_success'] = "ACL '{$name}' created.";
                    }
                } elseif ($action === 'delete') {
                    $id = (int) ($body['id'] ?? 0);
                    $aclRepo->delete($id);
                    $_SESSION['flash_success'] = 'ACL deleted.';
                }
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = $errPrefix . $e->getMessage();
            }

            return new Response(302, ['Location' => $pathAcls]);
        },
    ],
    // --- DNS Views (Split-Horizon) ---
    [
        'method'     => 'GET',
        'path'       => $pathViews,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var DnsViewRepository $viewRepo */
            $viewRepo = $c->get(DnsViewRepository::class);

            $flashSuccess = '';
            $flashError   = '';
            if (isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])) {
                $flashSuccess = $_SESSION['flash_success'];
                unset($_SESSION['flash_success']);
            }
            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }

            $html = View::render('views/index', [
                'views'        => $viewRepo->all(),
                'csrfToken'    => (string) ($_SESSION['_csrf']['value'] ?? ''),
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathViews,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($pathViews, $errPrefix): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var DnsViewRepository $viewRepo */
            $viewRepo = $c->get(DnsViewRepository::class);

            $body   = (array) ($req->getParsedBody() ?? []);
            $action = (string) ($body['_action'] ?? '');

            try {
                if ($action === 'create') {
                    $name         = trim((string) ($body['name'] ?? ''));
                    $matchClients = trim((string) ($body['match_clients'] ?? ''));
                    $rawDesc      = trim((string) ($body['description'] ?? ''));
                    $desc         = $rawDesc !== '' ? $rawDesc : null;

                    if ($name === '' || $matchClients === '') {
                        $_SESSION['flash_error'] = 'View name and match-clients are required.';
                    } elseif ($viewRepo->findByName($name) !== null) {
                        $_SESSION['flash_error'] = "View '{$name}' already exists.";
                    } else {
                        $viewRepo->create($name, $matchClients, $desc);
                        $_SESSION['flash_success'] = "View '{$name}' created.";
                    }
                } elseif ($action === 'delete') {
                    $id = (int) ($body['id'] ?? 0);
                    $viewRepo->delete($id);
                    $_SESSION['flash_success'] = 'View deleted.';
                }
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = $errPrefix . $e->getMessage();
            }

            return new Response(302, ['Location' => $pathViews]);
        },
    ],
    // --- DNSSEC ---
    [
        'method'     => 'GET',
        'path'       => $pathDnssec,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var DnssecKeyRepository $keyRepo */
            $keyRepo = $c->get(DnssecKeyRepository::class);
            /** @var ZoneRepository $zoneRepo */
            $zoneRepo = $c->get(ZoneRepository::class);

            $flashSuccess = '';
            $flashError   = '';
            if (isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])) {
                $flashSuccess = $_SESSION['flash_success'];
                unset($_SESSION['flash_success']);
            }
            if (isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])) {
                $flashError = $_SESSION['flash_error'];
                unset($_SESSION['flash_error']);
            }

            $html = View::render('dnssec/index', [
                'zones'        => $zoneRepo->all(),
                'keys'         => $keyRepo->all(),
                'csrfToken'    => (string) ($_SESSION['_csrf']['value'] ?? ''),
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathDnssec,
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use (
            $pathDnssec,
            $errPrefix,
            $generateDnssecKey
        ): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var DnssecKeyRepository $keyRepo */
            $keyRepo = $c->get(DnssecKeyRepository::class);
            /** @var ZoneFileService $zfs */
            $zfs = $c->get(ZoneFileService::class);

            $body   = (array) ($req->getParsedBody() ?? []);
            $action = (string) ($body['_action'] ?? '');

            try {
                if ($action === 'generate') {
                    $zoneId    = (int) ($body['zone_id'] ?? 0);
                    $keyRole   = (string) ($body['key_role'] ?? 'zsk');
                    $algorithm = (int) ($body['algorithm'] ?? 13);

                    if ($zoneId < 1) {
                        $_SESSION['flash_error'] = 'Please select a zone.';
                    } else {
                        $keyData = $generateDnssecKey($zoneId, $keyRole, $algorithm, $zfs->zonesDirectory());
                        $keyRepo->create(
                            $zoneId,
                            $keyRole,
                            $keyData['keyTag'],
                            $algorithm,
                            $keyData['keyFile'],
                            $keyData['publicKey']
                        );
                        $_SESSION['flash_success'] = 'DNSSEC key generated.';
                    }
                } elseif ($action === 'retire') {
                    $id = (int) ($body['id'] ?? 0);
                    $keyRepo->retire($id);
                    $_SESSION['flash_success'] = 'Key retired.';
                }
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = $errPrefix . $e->getMessage();
            }

            return new Response(302, ['Location' => $pathDnssec]);
        },
    ],
    // --- Webhooks ---
    [
        'method'     => 'GET',
        'path'       => $pathWebhooks,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var WebhookRepository $webhookRepo */
            $webhookRepo = $container->get(WebhookRepository::class);

            $flashSuccess = isset($_SESSION['flash_success']) && is_string($_SESSION['flash_success'])
                ? $_SESSION['flash_success'] : null;
            $flashError = isset($_SESSION['flash_error']) && is_string($_SESSION['flash_error'])
                ? $_SESSION['flash_error'] : null;
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);

            $csrfToken = (string) ($_SESSION['_csrf']['value'] ?? '');
            $html      = View::render('system/webhooks', [
                'webhooks'     => $webhookRepo->all(),
                'csrfToken'    => $csrfToken,
                'flashSuccess' => $flashSuccess,
                'flashError'   => $flashError,
            ]);

            return new Response(200, $htmlHeaders, $html);
        },
    ],
    [
        'method'     => 'POST',
        'path'       => $pathWebhooks,
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($pathWebhooks, $errPrefix): Response {
            /** @var Container $container */
            $container = $req->getAttribute('container');
            /** @var WebhookRepository $webhookRepo */
            $webhookRepo = $container->get(WebhookRepository::class);

            $body   = (array) ($req->getParsedBody() ?? []);
            $action = (string) ($body['_action'] ?? '');

            try {
                if ($action === 'create') {
                    $name   = trim((string) ($body['name'] ?? ''));
                    $url    = trim((string) ($body['url'] ?? ''));
                    $secret = isset($body['secret']) && $body['secret'] !== '' ? (string) $body['secret'] : null;
                    $events = trim((string) ($body['events'] ?? 'zone.updated'));

                    if ($name === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
                        $_SESSION['flash_error'] = 'Please provide a valid name and webhook HTTP/S URL.';
                    } else {
                        $webhookRepo->create($name, $url, $secret, $events);
                        $_SESSION['flash_success'] = 'Webhook endpoint registered.';
                    }
                } elseif ($action === 'delete') {
                    $id = (int) ($body['id'] ?? 0);
                    $webhookRepo->delete($id);
                    $_SESSION['flash_success'] = 'Webhook deleted.';
                }
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = $errPrefix . $e->getMessage();
            }

            return new Response(302, ['Location' => $pathWebhooks]);
        },
    ],
];
