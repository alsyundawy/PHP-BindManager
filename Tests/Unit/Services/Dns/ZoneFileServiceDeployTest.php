<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Dns;

use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use App\Services\Dns\ZoneFileService;
use PDO;
use PHPUnit\Framework\TestCase;

final class ZoneFileServiceDeployTest extends TestCase
{
    private string $tempDir;
    private PDO $pdo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/pbm_zones_test_' . uniqid();
        mkdir($this->tempDir, 0o755, true);

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
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '/*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        @rmdir($this->tempDir);
        parent::tearDown();
    }

    public function testDeployWritesZoneFileAndUpdatesStatus(): void
    {
        $zoneRepo   = new ZoneRepository($this->pdo);
        $recordRepo = new RecordRepository($this->pdo);

        $zoneId = $zoneRepo->create([
            'name'      => 'example.org.',
            'zone_type' => 'master',
            'file_path' => '/etc/bind/zones/db.example.org',
        ]);

        $recordRepo->create($zoneId, [
            'name'        => '@',
            'ttl'         => 3600,
            'record_type' => 'A',
            'content'     => '192.0.2.1',
        ]);

        $service = new ZoneFileService(
            $zoneRepo,
            $recordRepo,
            $this->tempDir,
            '/nonexistent/checkzone'
        );

        $result = $service->deploy($zoneId);

        self::assertTrue($result);
        $expectedFile = $this->tempDir . '/db.example.org';
        self::assertFileExists($expectedFile);

        $content = (string) file_get_contents($expectedFile);
        self::assertStringContainsString('$ORIGIN example.org.', $content);
        self::assertStringContainsString('@ 3600 IN A 192.0.2.1', $content);

        $updatedZone = $zoneRepo->find($zoneId);
        self::assertNotNull($updatedZone);
        self::assertSame('active', $updatedZone['status']);
    }

    public function testZoneRepositoryDelete(): void
    {
        $zoneRepo = new ZoneRepository($this->pdo);

        $zoneId = $zoneRepo->create([
            'name'      => 'to-delete.com.',
            'zone_type' => 'master',
            'file_path' => '/etc/bind/zones/db.to-delete.com',
        ]);

        self::assertNotNull($zoneRepo->find($zoneId));

        $zoneRepo->delete($zoneId);

        self::assertNull($zoneRepo->find($zoneId));
    }

    public function testRecordRepositoryAllAndFind(): void
    {
        $recordRepo = new RecordRepository($this->pdo);

        $recordId = $recordRepo->create(1, [
            'name'        => 'mail',
            'ttl'         => 1800,
            'record_type' => 'A',
            'content'     => '192.0.2.25',
        ]);

        $found = $recordRepo->find($recordId);
        self::assertNotNull($found);
        self::assertSame('mail', $found['name']);
        self::assertSame(1800, (int) $found['ttl']);

        $all = $recordRepo->all();
        self::assertCount(1, $all);
        self::assertSame('mail', $all[0]['name']);
    }
}
