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
        $basePath = rtrim((string) $this->config->get('logging.path', ''), '/');
        if ($basePath !== '' && ! is_dir($basePath)) {
            @mkdir($basePath, 0o750, true);
        }

        $levelName = strtoupper((string) $this->config->get('logging.level', 'WARNING'));
        $level     = match ($levelName) {
            'DEBUG'     => Level::Debug,
            'INFO'      => Level::Info,
            'NOTICE'    => Level::Notice,
            'ERROR'     => Level::Error,
            'CRITICAL'  => Level::Critical,
            'ALERT'     => Level::Alert,
            'EMERGENCY' => Level::Emergency,
            default     => Level::Warning,
        };

        $file = $basePath !== '' ? $basePath . '/' . $channel . '.log' : $channel . '.log';

        $handler = new StreamHandler($file, $level);
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));

        return new Logger($channel, [$handler]);
    }
}
