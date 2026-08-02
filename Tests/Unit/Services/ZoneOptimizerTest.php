<?php
declare(strict_types=1);
namespace Tests\Unit\Services;
use App\Services\System\ZoneOptimizer;
use PHPUnit\Framework\TestCase;
final class ZoneOptimizerTest extends TestCase{public function testNormalizesLineEndingsAndWhitespace(): void{$s=new ZoneOptimizer();$out=$s->normalize("a\r\nb   \n\n");self::assertSame("a\n\n",$out);}}
