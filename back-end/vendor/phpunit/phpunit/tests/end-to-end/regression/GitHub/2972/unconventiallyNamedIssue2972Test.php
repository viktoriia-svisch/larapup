<?php
namespace Issue2972;
use PHPUnit\Framework\TestCase;
class Issue2972Test extends TestCase
{
    public function testHello(): void
    {
        $this->assertNotEmpty('Hello world!');
    }
}
