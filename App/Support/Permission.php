<?php

declare(strict_types=1);

namespace App\Support;

final class Permission
{
    /**
     * @param array<string, mixed> $rolesConfig
     */
    public static function allows(array $rolesConfig, string $role, string $ability): bool
    {
        $roles       = is_array($rolesConfig['roles'] ?? null) ? $rolesConfig['roles'] : [];
        $permissions = is_array($roles[$role] ?? null) ? $roles[$role] : [];

        if (in_array('*', $permissions, true) || in_array($ability, $permissions, true)) {
            return true;
        }

        foreach ($permissions as $permission) {
            if (
                is_string($permission)
                && str_ends_with($permission, '.*')
                && str_starts_with($ability, substr($permission, 0, -1))
            ) {
                return true;
            }
        }

        return false;
    }
}
