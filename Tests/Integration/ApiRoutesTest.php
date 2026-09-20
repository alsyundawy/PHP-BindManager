<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Container\Container;
use App\Http\Router;
use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use App\Services\Dns\ZoneFileService;
use Nyholm\Psr7\ServerRequest;
use PDO;
use PHPUnit\Framework\TestCase;

final class ApiRoutesTest extends TestCase
{
    private Container $container;
    private Router $router;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->pdo->exec('CREATE TABLE zones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            zone_type TEXT NOT NULL,
            file_path TEXT NOT NULL,
            view_id INTEGER DEFAULT NULL,
            status TEXT NOT NULL DEFAULT \'draft\',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        $this->pdo->exec('CREATE TABLE dns_records (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            zone_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            record_type TEXT NOT NULL,
            ttl INTEGER NOT NULL DEFAULT 3600,
            priority INTEGER DEFAULT NULL,
            content TEXT NOT NULL,
            disabled INTEGER NOT NULL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT DEFAULT CURRENT_TIMESTAMP
        )');

        $this->container = new Container();
        $this->container->set(ZoneRepository::class, fn () => new ZoneRepository($this->pdo));
        $this->container->set(RecordRepository::class, fn () => new RecordRepository($this->pdo));
        $this->container->set(ZoneFileService::class, fn () => new ZoneFileService(
            $this->container->get(ZoneRepository::class),
            $this->container->get(RecordRepository::class),
            sys_get_temp_dir(),
            '/nonexistent/checkzone'
        ));

        $this->router = Router::fromFile(dirname(__DIR__, 2) . '/Routes/api.php');
    }

    public function testGetSystemHealth(): void
    {
        $req   = new ServerRequest('GET', '/api/v1/system/health')->withAttribute('container', $this->container);
        $match = $this->router->match($req);
        $res   = $match->dispatch($match->request);

        self::assertSame(200, $res->getStatusCode());
        $body = json_decode((string) $res->getBody(), true);
        self::assertSame('ok', $body['status']);
        self::assertSame('connected', $body['database']);
    }

    public function testZonesCrudApi(): void
    {
        // 1. Initially empty
        $req   = new ServerRequest('GET', '/api/v1/zones')->withAttribute('container', $this->container);
        $match = $this->router->match($req);
        $res   = $match->dispatch($match->request);
        self::assertSame(200, $res->getStatusCode());
        $body = json_decode((string) $res->getBody(), true);
        self::assertSame(0, $body['total']);

        // 2. Create zone
        $createReq = new ServerRequest('POST', '/api/v1/zones')
            ->withAttribute('container', $this->container)
            ->withParsedBody(['name' => 'api-test.com', 'zone_type' => 'master']);
        $match = $this->router->match($createReq);
        $res   = $match->dispatch($match->request);
        self::assertSame(201, $res->getStatusCode());
        $created = json_decode((string) $res->getBody(), true);
        $zoneId  = (int) $created['id'];
        self::assertGreaterThan(0, $zoneId);

        // 3. Get single zone
        $getReq = new ServerRequest('GET', "/api/v1/zones/{$zoneId}")
            ->withAttribute('container', $this->container);
        $match = $this->router->match($getReq);
        $res   = $match->dispatch($match->request);
        self::assertSame(200, $res->getStatusCode());
        $zoneData = json_decode((string) $res->getBody(), true);
        self::assertSame('api-test.com', $zoneData['zone']['name']);

        // 4. Create record in zone
        $createRecReq = new ServerRequest('POST', '/api/v1/records')
            ->withAttribute('container', $this->container)
            ->withParsedBody([
                'zone_id'     => $zoneId,
                'name'        => '@',
                'record_type' => 'A',
                'ttl'         => 3600,
                'content'     => '192.0.2.1',
            ]);
        $match = $this->router->match($createRecReq);
        $res   = $match->dispatch($match->request);
        self::assertSame(201, $res->getStatusCode());
        $recData  = json_decode((string) $res->getBody(), true);
        $recordId = (int) $recData['id'];

        // 5. Deploy zone
        $deployReq = new ServerRequest('POST', "/api/v1/zones/{$zoneId}/deploy")
            ->withAttribute('container', $this->container);
        $match = $this->router->match($deployReq);
        $res   = $match->dispatch($match->request);
        self::assertSame(200, $res->getStatusCode());
        $deployData = json_decode((string) $res->getBody(), true);
        self::assertSame('deployed', $deployData['status']);

        // 6. Delete record
        $delRecReq = new ServerRequest('DELETE', "/api/v1/records/{$recordId}")
            ->withAttribute('container', $this->container);
        $match = $this->router->match($delRecReq);
        $res   = $match->dispatch($match->request);
        self::assertSame(200, $res->getStatusCode());

        // 7. Delete zone
        $delZoneReq = new ServerRequest('DELETE', "/api/v1/zones/{$zoneId}")
            ->withAttribute('container', $this->container);
        $match = $this->router->match($delZoneReq);
        $res   = $match->dispatch($match->request);
        self::assertSame(200, $res->getStatusCode());
    }
}
