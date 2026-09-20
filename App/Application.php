<?php

declare(strict_types=1);

namespace App;

use App\Container\Container;
use App\Database\ConnectionFactory;
use App\Exceptions\HttpException;
use App\Http\Kernel;
use App\Http\Router;
use App\Logging\LoggerFactory;
use App\Repositories\Auth\LoginAttemptRepository;
use App\Repositories\Auth\RoleRepository;
use App\Repositories\Auth\SessionRepository;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Api\ApiTokenRepository;
use App\Repositories\Dns\AclRepository;
use App\Repositories\Dns\DnssecKeyRepository;
use App\Repositories\Dns\DnsViewRepository;
use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use App\Repositories\System\ActivityLogRepository;
use App\Repositories\System\AuditLogRepository;
use App\Repositories\System\BackupRepository;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\CsrfService;
use App\Services\Auth\RateLimiterService;
use App\Services\Dns\ZoneFileService;
use App\Services\System\BackupService;
use App\Support\Config;
use App\Support\Env;
use App\Support\Path;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class Application
{
    private function __construct(
        private readonly string $basePath,
        private readonly Container $container,
        private readonly Kernel $kernel,
    ) {
    }

    public static function boot(string $basePath): self
    {
        Path::bootstrap($basePath);
        Env::load(Path::base('.env'));

        $config = new Config([
            'app'      => require_once Path::config('app.php'),
            'database' => require_once Path::config('database.php'),
            'session'  => require_once Path::config('session.php'),
            'security' => require_once Path::config('security.php'),
            'bind9'    => require_once Path::config('bind9.php'),
            'logging'  => require_once Path::config('logging.php'),
            'cache'    => require_once Path::config('cache.php'),
            'api'      => require_once Path::config('api.php'),
            'rbac'     => require_once Path::config('rbac.php'),
        ]);

        $container = new Container();
        $container->set(Config::class, static fn () => $config);
        $container->set(Psr17Factory::class, static fn (): Psr17Factory => new Psr17Factory());
        $container->set(
            ConnectionFactory::class,
            static fn (): ConnectionFactory => new ConnectionFactory($config)
        );
        $container->set(
            LoggerFactory::class,
            static fn (): LoggerFactory => new LoggerFactory($config)
        );
        $container->set(
            SessionRepository::class,
            static fn (Container $c): SessionRepository => new SessionRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            UserRepository::class,
            static fn (Container $c): UserRepository => new UserRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            RoleRepository::class,
            static fn (Container $c): RoleRepository => new RoleRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            LoginAttemptRepository::class,
            static fn (Container $c): LoginAttemptRepository => new LoginAttemptRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            RateLimiterService::class,
            static fn (Container $c): RateLimiterService => new RateLimiterService(
                $c->get(LoginAttemptRepository::class),
                $c->get(Config::class)
            )
        );
        $container->set(
            CsrfService::class,
            static fn (Container $c): CsrfService => new CsrfService($c->get(Config::class))
        );
        $container->set(
            AuthenticationService::class,
            static fn (Container $c): AuthenticationService => new AuthenticationService(
                $c->get(UserRepository::class),
                $c->get(SessionRepository::class),
                $c->get(RateLimiterService::class),
                $c->get(Config::class)
            )
        );
        $container->set(
            ZoneRepository::class,
            static fn (Container $c): ZoneRepository => new ZoneRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            RecordRepository::class,
            static fn (Container $c): RecordRepository => new RecordRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            ZoneFileService::class,
            static function (Container $c): ZoneFileService {
                /** @var Config $config */
                $config     = $c->get(Config::class);
                $defaultDir = (string) $config->get('bind9.zones_directory', '/etc/bind/zones');
                $zonesDir   = (string) ($config->get('bind9.zones_dir') ?? $defaultDir);
                $chkZone    = (string) $config->get('bind9.checkzone', '/usr/sbin/named-checkzone');

                return new ZoneFileService(
                    $c->get(ZoneRepository::class),
                    $c->get(RecordRepository::class),
                    $zonesDir,
                    $chkZone,
                );
            }
        );
        $container->set(
            ActivityLogRepository::class,
            static fn (Container $c): ActivityLogRepository => new ActivityLogRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            AuditLogRepository::class,
            static fn (Container $c): AuditLogRepository => new AuditLogRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            ApiTokenRepository::class,
            static fn (Container $c): ApiTokenRepository => new ApiTokenRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            BackupRepository::class,
            static fn (Container $c): BackupRepository => new BackupRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            BackupService::class,
            static function (Container $c): BackupService {
                /** @var Config $cfg */
                $cfg       = $c->get(Config::class);
                $backupDir = (string) $cfg->get('database.backup_directory', Path::base('storage/backups'));

                return new BackupService(
                    $c->get(ConnectionFactory::class)->create(),
                    $backupDir,
                );
            }
        );
        $container->set(
            AclRepository::class,
            static fn (Container $c): AclRepository => new AclRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            DnsViewRepository::class,
            static fn (Container $c): DnsViewRepository => new DnsViewRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            DnssecKeyRepository::class,
            static fn (Container $c): DnssecKeyRepository => new DnssecKeyRepository(
                $c->get(ConnectionFactory::class)->create()
            )
        );
        $container->set(
            Router::class,
            static function (): Router {
                return Router::loadFromFiles([
                    Path::routes('web.php'),
                    Path::routes('dns.php'),
                    Path::routes('system.php'),
                    Path::routes('api.php'),
                ]);
            }
        );
        $container->set(
            Kernel::class,
            static fn (Container $c): Kernel => new Kernel($c)
        );

        return new self(
            $basePath,
            $container,
            $container->get(Kernel::class)
        );
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function handleCurrentRequest(): ResponseInterface
    {
        $factory = $this->container->get(Psr17Factory::class);
        $creator = new ServerRequestCreator($factory, $factory, $factory, $factory);
        $request = $creator->fromGlobals();

        return $this->handle($request);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->kernel->handle($request);
        } catch (HttpException $exception) {
            return $this->kernel->errorResponse($exception->getStatusCode(), $exception->getMessage());
        } catch (\Throwable $throwable) {
            return $this->kernel->errorResponse(500, 'Internal Server Error');
        }
    }

    public function isSecureRequest(): bool
    {
        $https          = $_SERVER['HTTPS']                  ?? '';
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

        return $https === 'on' || $https === '1' || strtolower($forwardedProto) === 'https';
    }
}
