<?php
use PHPUnit\Framework\TestCase;
class Issue2366
{
    public function foo(): bool
    {
        return false;
    }
}
class Issue2366Test extends TestCase
{
    public function testOne($o): void
    {
        $this->assertEquals(1, $o->foo());
    }
    public function provider()
    {
        $o = $this->createMock(Issue2366::class);
        $o->method('foo')->willReturn(1);
        return [
            [$o],
            [$o],
        ];
    }
}
