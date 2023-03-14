<?php
class Issue2811Test extends PHPUnit\Framework\TestCase
{
    public function testOne(): void
    {
        $this->expectExceptionMessage('hello');
        throw new \Exception('hello');
    }
}
