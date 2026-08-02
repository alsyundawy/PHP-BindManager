<?php
declare(strict_types=1);
namespace Tests\Functional;
use PHPUnit\Framework\TestCase;
final class FinalAuditSmokeTest extends TestCase{public function testAuditDocumentExists(): void{self::assertFileExists(dirname(__DIR__,2)."/Fase-6-QA-Testing-Final-Audit.md");}}
