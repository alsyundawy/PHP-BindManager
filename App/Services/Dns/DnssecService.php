<?php

declare(strict_types=1);

namespace App\Services\Dns;

use RuntimeException;

final class DnssecService
{
    public function __construct(
        private readonly string $keygenBinary   = '/usr/sbin/dnssec-keygen',
        private readonly string $signzoneBinary = '/usr/sbin/dnssec-signzone',
    ) {
    }

    public function validateBinaries(): void
    {
        foreach ([$this->keygenBinary, $this->signzoneBinary] as $binary) {
            if (! is_executable($binary)) {
                throw new RuntimeException('DNSSEC binary is unavailable: ' . $binary);
            }
        }
    }
}
