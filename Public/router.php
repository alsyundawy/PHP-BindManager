<?php

declare(strict_types=1);

// Built-in PHP web server router for local development and testing
$uri  = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . $uri;

if ($uri !== '/' && file_exists($file) && ! is_dir($file)) {
    return false;
}

require_once __DIR__ . '/index.php';
