<?php
use PHPUnit\Framework\TestCase;
class Issue2380Test extends TestCase
{
    public function testGeneratorProvider($data): void
    {
        $this->assertNotEmpty($data);
    }
    public function generatorData()
    {
        yield ['testing'];
    }
}
