<?php

declare(strict_types=1);

namespace App\Services\Dns;

use App\Repositories\Dns\RecordRepository;
use App\Repositories\Dns\ZoneRepository;
use RuntimeException;

final class ZoneFileService
{
    public function __construct(
        private readonly ZoneRepository    $zones,
        private readonly RecordRepository  $records,
        private readonly string            $zonesDirectory,
        private readonly string            $checkzoneBinary = '/usr/sbin/named-checkzone',
    ) {
    }

    public function export(int $zoneId): string
    {
        $zone = $this->zones->find($zoneId);

        if ($zone === null) {
            throw new RuntimeException('Zone not found.');
        }

        $rows = $this->records->forZone($zoneId);
        $out  = '$ORIGIN ' . rtrim((string) $zone['name'], '.') . ".\n\$TTL 3600\n";

        foreach ($rows as $r) {
            $out .= sprintf(
                "%s %d IN %s %s\n",
                $r['name'],
                $r['ttl'],
                $r['record_type'],
                $r['content'],
            );
        }

        return $out;
    }

    public function validateText(string $zoneName, string $zoneText): bool
    {
        $tmp = tempnam(sys_get_temp_dir(), 'pbm-zone-');

        if ($tmp === false) {
            throw new RuntimeException('Unable to create temporary zone file.');
        }

        try {
            file_put_contents($tmp, $zoneText);

            $command = [$this->checkzoneBinary, $zoneName, $tmp];
            $descriptors = [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];

            $process = proc_open($command, $descriptors, $pipes);

            if (! is_resource($process)) {
                throw new RuntimeException('Unable to execute named-checkzone.');
            }

            $stdout = (string) stream_get_contents($pipes[1]);
            $stderr = (string) stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $code = proc_close($process);

            if ($code !== 0) {
                throw new RuntimeException(trim($stderr !== '' ? $stderr : ($stdout !== '' ? $stdout : 'Zone validation failed.')));
            }

            return true;
        } finally {
            @unlink($tmp);
        }
    }
}
