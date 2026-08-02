<?php
declare(strict_types=1);
namespace Tests\Unit\Services;
use App\Services\System\ApiTokenService;
use PDO;
use PHPUnit\Framework\TestCase;
final class ApiTokenServiceTest extends TestCase{public function testIssueReturnsPlainToken(): void{$pdo=new PDO("sqlite::memory:");$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$pdo->exec("CREATE TABLE api_tokens(id INTEGER PRIMARY KEY AUTOINCREMENT,user_id INTEGER,name TEXT,token_hash TEXT,scopes TEXT,expires_at TEXT,last_used_at TEXT,revoked_at TEXT,created_at TEXT DEFAULT CURRENT_TIMESTAMP)");$svc=new ApiTokenService($pdo);$r=$svc->issue(1,"ci",["zones:read"]);self::assertStringStartsWith("pbm_",$r["token"]);self::assertGreaterThan(0,$r["id"]);}}
