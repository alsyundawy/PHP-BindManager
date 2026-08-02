<?php
declare(strict_types=1);
namespace Tests\Unit\Support;
use App\Support\Permission;
use PHPUnit\Framework\TestCase;
final class PermissionTest extends TestCase{public function testAllowsWildcardPermission(): void{$config=["roles"=>["admin"=>["*"],"viewer"=>["zones.view"]]];self::assertTrue(Permission::allows($config,"admin","anything.anywhere"));self::assertFalse(Permission::allows($config,"viewer","zones.update"));}}
