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
use App\Services\Dns\ZoneFileService;
use App\Services\System\BackupService;
use App\Support\Config;
use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

$htmlHeaders = ['Content-Type' => 'text/html; charset=UTF-8'];

return [
    // ─── System Health / Overview ──────────────────────────────────────────────
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
    // ─── API Docs ──────────────────────────────────────────────────────────────
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
    // ─── Backups ───────────────────────────────────────────────────────────────
    [
        'method'     => 'GET',
        'path'       => '/system/backups',
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
        'path'       => '/system/backups',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
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
                $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            }

            return new Response(302, ['Location' => '/system/backups']);
        },
    ],
    // ─── Activity Log ──────────────────────────────────────────────────────────
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
    // ─── Audit Trail ───────────────────────────────────────────────────────────
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
    // ─── API Tokens ────────────────────────────────────────────────────────────
    [
        'method'     => 'GET',
        'path'       => '/system/tokens',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req) use ($htmlHeaders): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var ApiTokenRepository $tokenRepo */
            $tokenRepo = $c->get(ApiTokenRepository::class);

            $userId = (int) ($_SESSION['user_id'] ?? 0);
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
        'path'       => '/system/tokens',
        'auth'       => true,
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
            /** @var Container $c */
            $c = $req->getAttribute('container');
            /** @var ApiTokenRepository $tokenRepo */
            $tokenRepo = $c->get(ApiTokenRepository::class);

            $body     = (array) ($req->getParsedBody() ?? []);
            $action   = (string) ($body['_action'] ?? '');
            $userId   = (int) ($_SESSION['user_id'] ?? 0);

            try {
                if ($action === 'create') {
                    $name      = trim((string) ($body['name'] ?? ''));
                    $expiresAt = trim((string) ($body['expires_at'] ?? '')) ?: null;
                    $rawScopes = isset($body['scopes']) && is_array($body['scopes'])
                        ? array_map('strval', $body['scopes'])
                        : [];

                    if ($name === '') {
                        $_SESSION['flash_error'] = 'Token name is required.';
                    } else {
                        $plainToken = bin2hex(random_bytes(32));
                        $hash       = hash('sha256', $plainToken);
                        $tokenRepo->create($userId, $name, $hash, $rawScopes, $expiresAt);
                        $_SESSION['new_api_token']  = $plainToken;
                        $_SESSION['flash_success']  = "Token '{$name}' created.";
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
                $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            }

            return new Response(302, ['Location' => '/system/tokens']);
        },
    ],
    // ─── ACL Management ────────────────────────────────────────────────────────
    [
        'method'     => 'GET',
        'path'       => '/acls',
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
        'path'       => '/acls',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
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
                    $desc    = trim((string) ($body['description'] ?? '')) ?: null;

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
                $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            }

            return new Response(302, ['Location' => '/acls']);
        },
    ],
    // ─── DNS Views (Split-Horizon) ─────────────────────────────────────────────
    [
        'method'     => 'GET',
        'path'       => '/views',
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
        'path'       => '/views',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
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
                    $desc         = trim((string) ($body['description'] ?? '')) ?: null;

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
                $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            }

            return new Response(302, ['Location' => '/views']);
        },
    ],
    // ─── DNSSEC ────────────────────────────────────────────────────────────────
    [
        'method'     => 'GET',
        'path'       => '/dnssec',
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
        'path'       => '/dnssec',
        'auth'       => true,
        'role'       => 'admin',
        'rate_limit' => 'web',
        'handler'    => static function (ServerRequestInterface $req): Response {
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
                        // Generate key using dnssec-keygen if available; store result in DB
                        $zonesDir  = $zfs->zonesDirectory();
                        $keyFile   = 'K' . $zoneId . '-' . $keyRole . '-' . time();
                        $keyTag    = random_int(1, 65535);
                        $publicKey = null;

                        $dnssecKeygen = '/usr/sbin/dnssec-keygen';
                        if (is_executable($dnssecKeygen)) {
                            $roleFlag  = $keyRole === 'ksk' ? ' -f KSK' : '';
                            $cmd       = escapeshellcmd($dnssecKeygen)
                                       . $roleFlag
                                       . ' -a ' . (int) $algorithm
                                       . ' -n ZONE'
                                       . ' -K ' . escapeshellarg($zonesDir)
                                       . ' zone' . $zoneId;
                            $output    = [];
                            $retCode   = 0;
                            exec($cmd . ' 2>&1', $output, $retCode);
                            if ($retCode === 0 && isset($output[0])) {
                                $keyFile = (string) $output[0];
                                $pubFile = $zonesDir . '/' . $keyFile . '.key';
                                if (is_file($pubFile)) {
                                    $publicKey = file_get_contents($pubFile) ?: null;
                                    // Extract key tag from filename (Kexample.+NNN+TTTTT)
                                    if (preg_match('/\+(\d{5})$/', $keyFile, $m)) {
                                        $keyTag = (int) $m[1];
                                    }
                                }
                            }
                        }

                        $keyRepo->create($zoneId, $keyRole, $keyTag, $algorithm, $keyFile, $publicKey);
                        $_SESSION['flash_success'] = 'DNSSEC key generated.';
                    }
                } elseif ($action === 'retire') {
                    $id = (int) ($body['id'] ?? 0);
                    $keyRepo->retire($id);
                    $_SESSION['flash_success'] = 'Key retired.';
                }
            } catch (\Throwable $e) {
                $_SESSION['flash_error'] = 'Error: ' . $e->getMessage();
            }

            return new Response(302, ['Location' => '/dnssec']);
        },
    ],
];
