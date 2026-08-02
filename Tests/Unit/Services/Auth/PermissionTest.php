<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Auth;

use App\Support\Permission;
use PHPUnit\Framework\TestCase;

final class PermissionTest extends TestCase
{
    public function testWildcardPermissionMatchesAbility(): void
    {
        $config = ['roles' => ['editor' => ['zones.*'], 'viewer' => ['zones.view']]];
        self::assertTrue(Permission::allows($config, 'editor', 'zones.update'));
        self::assertFalse(Permission::allows($config, 'viewer', 'zones.update'));
    }
}
