<?php

declare(strict_types=1);

use App\Application;
use App\Http\SecurityHeaders;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = Application::boot(dirname(__DIR__));
$response = $app->handleCurrentRequest();

foreach (SecurityHeaders::forResponse($app->isSecureRequest()) as $header => $value) {
    header($header . ': ' . $value, true);
}

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header($name . ': ' . $value, false);
    }
}

echo $response->getBody();
