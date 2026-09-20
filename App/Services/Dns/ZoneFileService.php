<?php

declare(strict_types=1);

namespace App\Services\Dns;

use App\Exceptions\ZoneException;
use App\Exceptions\ZoneValidationException;
use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;

final class ZoneFileService
{
    public function __construct(
        private readonly ZoneRepository $zones,
        private readonly RecordRepository $records,
        private readonly string $zonesDirectory,
        private readonly string $checkzoneBinary = '/usr/sbin/named-checkzone',
    ) {
    }

    public function zonesDirectory(): string
    {
        return $this->zonesDirectory;
    }

    public function export(int $zoneId): string
    {
        $zone = $this->zones->find($zoneId);

        if ($zone === null) {
            throw new ZoneException('Zone not found.');
        }

        $rows = $this->records->forZone($zoneId);
        $out  = '$ORIGIN ' . rtrim((string) $zone['name'], '.') . ".\n\$TTL 3600\n";

        foreach ($rows as $r) {
            $content = (string) $r['content'];
            if ($r['priority'] !== null && in_array($r['record_type'], ['MX', 'SRV'], true)) {
                $pfx = (string) $r['priority'] . ' ';
                if (! str_starts_with($content, $pfx)) {
                    $content = $pfx . $content;
                }
            }

            $out .= sprintf(
                "%s %d IN %s %s\n",
                $r['name'],
                $r['ttl'],
                $r['record_type'],
                $content,
            );
        }

        return $out;
    }

    public function validateText(string $zoneName, string $zoneText): bool
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pbm-zone-');

        if ($tmp === false) {
            throw new ZoneException('Unable to create temporary zone file.');
        }

        try {
            file_put_contents($tmp, $zoneText);

            $command     = [$this->checkzoneBinary, $zoneName, $tmp];
            $descriptors = [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $process = proc_open($command, $descriptors, $pipes);

            if (! is_resource($process)) {
                throw new ZoneException('Unable to execute named-checkzone.');
            }

            $stdout = (string) stream_get_contents($pipes[1]);
            $stderr = (string) stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($process);

            if ($code !== 0) {
                $errorMsg = 'Zone validation failed.';
                if ($stderr !== '') {
                    $errorMsg = $stderr;
                } elseif ($stdout !== '') {
                    $errorMsg = $stdout;
                }

                throw new ZoneValidationException(trim($errorMsg));
            }

            return true;
        } finally {
            @unlink($tmp);
        }
    }

    public function deploy(int $zoneId): bool
    {
        $zone = $this->zones->find($zoneId);

        if ($zone === null) {
            throw new ZoneException('Zone not found.');
        }

        $zoneName = (string) $zone['name'];
        $zoneText = $this->export($zoneId);

        if (is_executable($this->checkzoneBinary)) {
            $this->validateText($zoneName, $zoneText);
        }

        if (
            ! is_dir($this->zonesDirectory)
            && ! @mkdir($this->zonesDirectory, 0o755, true)
            && ! is_dir($this->zonesDirectory)
        ) {
            throw new ZoneException("Cannot create zones directory: {$this->zonesDirectory}");
        }

        $cleanName = ltrim(rtrim($zoneName, '.'), '.');
        $filePath  = rtrim($this->zonesDirectory, '/') . '/db.' . $cleanName;

        if (file_put_contents($filePath, $zoneText) === false) {
            throw new ZoneException("Failed to write zone file: {$filePath}");
        }

        $this->zones->updateStatus($zoneId, 'active');

        return true;
    }
}
