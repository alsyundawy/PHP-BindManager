<?php
declare(strict_types=1);
namespace Tests\Integration;
use PHPUnit\Framework\TestCase;
final class PhpStanConfigTest extends TestCase{public function testConfigFilesExist(): void{self::assertFileExists(dirname(__DIR__,2)."/phpstan.neon");self::assertFileExists(dirname(__DIR__,2)."/psalm.xml");self::assertFileExists(dirname(__DIR__,2)."/phpcs.xml");}}
