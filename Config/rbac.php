<?php

declare(strict_types=1);

return [
    'roles' => [
        'admin' => ['*'],
        'editor' => [
            'dashboard.view',
            'zones.view',
            'zones.create',
            'zones.update',
            'records.view',
            'records.create',
            'records.update',
            'records.delete',
            'system.view',
        ],
        'viewer' => [
            'dashboard.view',
            'zones.view',
            'records.view',
            'system.view',
        ],
    ],
];
