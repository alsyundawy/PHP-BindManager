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
use App\Repositories\Auth\SessionRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\CsrfService;
use App\Services\Auth\RateLimiterService;
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
            'app' => require Path::config('app.php'),
            'database' => require Path::config('database.php'),
            'session' => require Path::config('session.php'),
            'security' => require Path::config('security.php'),
            'bind9' => require Path::config('bind9.php'),
            'logging' => require Path::config('logging.php'),
            'cache' => require Path::config('cache.php'),
            'api' => require Path::config('api.php'),
            'rbac' => require Path::config('rbac.php'),
        ]);

        $container = new Container();
        $container->set(Config::class, static fn () => $config);
        $container->set(Psr17Factory::class, static fn () => new Psr17Factory());
        $container->set(ConnectionFactory::class, static fn () => new ConnectionFactory($config));
        $container->set(LoggerFactory::class, static fn () => new LoggerFactory($config));
        $container->set(SessionRepository::class, static fn (Container $c) => new SessionRepository($c->get(ConnectionFactory::class)->create()));
        $container->set(UserRepository::class, static fn (Container $c) => new UserRepository($c->get(ConnectionFactory::class)->create()));
        $container->set(LoginAttemptRepository::class, static fn (Container $c) => new LoginAttemptRepository($c->get(ConnectionFactory::class)->create()));
        $container->set(RateLimiterService::class, static fn (Container $c) => new RateLimiterService(
            $c->get(LoginAttemptRepository::class),
            $c->get(Config::class)
        ));
        $container->set(CsrfService::class, static fn (Container $c) => new CsrfService($c->get(Config::class)));
        $container->set(AuthenticationService::class, static fn (Container $c) => new AuthenticationService(
            $c->get(UserRepository::class),
            $c->get(SessionRepository::class),
            $c->get(RateLimiterService::class),
            $c->get(Config::class)
        ));
        $container->set(Router::class, static fn () => Router::fromFile(Path::routes('web.php')));
        $container->set(Kernel::class, static fn (Container $c) => new Kernel($c));

        return new self(
            $basePath,
            $container,
            $container->get(Kernel::class)
        );
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
        $https = $_SERVER['HTTPS'] ?? '';
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';

        return $https === 'on' || $https === '1' || strtolower((string) $forwardedProto) === 'https';
    }
}
