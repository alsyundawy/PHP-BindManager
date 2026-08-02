<?php

declare(strict_types=1);

use App\Support\Env;

return ['backup_path'=>Env::get('BACKUP_PATH',dirname(__DIR__).'/Storage/Backups'),'database_path'=>Env::get('DB_PATH',dirname(__DIR__).'/Database/bindmanager.sqlite'),'retention_days'=>(int)Env::get('BACKUP_RETENTION_DAYS',30)];
