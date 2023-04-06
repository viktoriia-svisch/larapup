<?php
class Issue2731Test extends PHPUnit\Framework\TestCase
{
    public function testOne(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('');
        throw new Exception('message');
    }
}
