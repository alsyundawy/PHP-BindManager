<?php

declare(strict_types=1);

namespace App\Services\System;

final class ZoneOptimizationService
{
    public function normalizeSerial(int $serial):int{$today=(int)date('Ymd').'00';return max($serial+1,$today);}
    public function normalizeTtl(int $ttl):int{return max(1,min($ttl,2147483647));}
    public function normalizeOwner(string $owner):string{$owner=trim($owner);return $owner===''?'@':rtrim($owner,'.');}
}
