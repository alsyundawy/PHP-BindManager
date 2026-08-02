<?php

declare(strict_types=1);

namespace App\Support;

final class Permission
{
    public static function allows(array $rolesConfig, string $role, string $ability): bool
    {
        $permissions = $rolesConfig['roles'][$role] ?? [];
        if (in_array('*', $permissions, true)) {
            return true;
        }

        if (in_array($ability, $permissions, true)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (str_ends_with($permission, '.*') && str_starts_with($ability, substr($permission, 0, -1))) {
                return true;
            }
        }

        return false;
    }
}
