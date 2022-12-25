<?php
use PHPUnit\Framework\TestCase;
class ProxyObjectTest extends TestCase
{
    public function testMockedMethodIsProxiedToOriginalMethod(): void
    {
        $proxy = $this->getMockBuilder(Bar::class)
                      ->enableProxyingToOriginalMethods()
                      ->getMock();
        $proxy->expects($this->once())
              ->method('doSomethingElse');
        $foo = new Foo;
        $this->assertEquals('result', $foo->doSomething($proxy));
    }
    public function testMockedMethodWithReferenceIsProxiedToOriginalMethod(): void
    {
        $proxy = $this->getMockBuilder(MethodCallbackByReference::class)
                      ->enableProxyingToOriginalMethods()
                      ->getMock();
        $a = $b = $c = 0;
        $proxy->callback($a, $b, $c);
        $this->assertEquals(1, $b);
    }
}
