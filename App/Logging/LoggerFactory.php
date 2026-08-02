<?php

declare(strict_types=1);

namespace App\Logging;

use App\Support\Config;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class LoggerFactory
{
    public function __construct(private readonly Config $config)
    {
    }

    public function make(string $channel): Logger
    {
        $basePath = rtrim((string) $this->config->get('logging.path'), '/');
        $level = Level::fromName(strtoupper((string) $this->config->get('logging.level', 'warning')));
        $file = $basePath . '/' . $channel . '.log';

        $handler = new StreamHandler($file, $level);
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));

        return new Logger($channel, [$handler]);
    }
}
