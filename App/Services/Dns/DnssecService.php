<?php

declare(strict_types=1);

namespace App\Services\Dns;

use App\Exceptions\DnssecBinaryException;

final class DnssecService
{
    public function __construct(
        private readonly string $keygenBinary = '/usr/bin/dnssec-keygen',
        private readonly string $signzoneBinary = '/usr/bin/dnssec-signzone',
    ) {
    }

    public function validateBinaries(): void
    {
        foreach ([$this->keygenBinary, $this->signzoneBinary] as $binary) {
            if (! is_executable($binary)) {
                throw new DnssecBinaryException('DNSSEC binary is unavailable: ' . $binary);
            }
        }
    }
}
