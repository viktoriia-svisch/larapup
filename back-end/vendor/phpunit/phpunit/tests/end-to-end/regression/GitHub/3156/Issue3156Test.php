<?php
declare(strict_types=1);
namespace Test;
use PHPUnit\Framework\TestCase;
use stdClass;
class Issue3156Test extends TestCase
{
    public function testConstants(): stdClass
    {
        $this->assertStringEndsWith('/', '/');
        return new stdClass;
    }
    public function dataSelectOperatorsProvider(): array
    {
        return [
            ['1'],
            ['2'],
        ];
    }
    public function testDependsRequire(string $val, stdClass $obj): void
    {
        $this->assertStringEndsWith('/', '/');
    }
}
