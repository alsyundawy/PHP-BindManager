<?php

declare(strict_types=1);

namespace App\Services\System;

final class ZoneOptimizer
{
    public function normalize(string $zoneText):string{$zoneText=str_replace(["
",""],"
",$zoneText);$zoneText=preg_replace('/[ 	]+$/m','',$zoneText)??$zoneText;$zoneText=preg_replace("/
{3,}/","

",$zoneText)??$zoneText;return rtrim($zoneText)."
";}
}
