<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/App');
define('CONFIG_PATH', BASE_PATH . '/Config');
define('STORAGE_PATH', BASE_PATH . '/Storage');
define('DATABASE_PATH', BASE_PATH . '/Database');
define('PUBLIC_PATH', BASE_PATH . '/Public');
define('RESOURCE_PATH', BASE_PATH . '/Resources');

require_once BASE_PATH . '/vendor/autoload.php';
