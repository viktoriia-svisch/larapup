<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Proxy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;
class AbstractProxyTest extends TestCase
{
    protected $proxy;
    protected function setUp()
    {
        $this->proxy = $this->getMockForAbstractClass(AbstractProxy::class);
    }
    protected function tearDown()
    {
        $this->proxy = null;
    }
    public function testGetSaveHandlerName()
    {
        $this->assertNull($this->proxy->getSaveHandlerName());
    }
    public function testIsSessionHandlerInterface()
    {
        $this->assertFalse($this->proxy->isSessionHandlerInterface());
        $sh = new SessionHandlerProxy(new \SessionHandler());
        $this->assertTrue($sh->isSessionHandlerInterface());
    }
    public function testIsWrapper()
    {
        $this->assertFalse($this->proxy->isWrapper());
    }
    public function testIsActive()
    {
        $this->assertFalse($this->proxy->isActive());
        session_start();
        $this->assertTrue($this->proxy->isActive());
    }
    public function testName()
    {
        $this->assertEquals(session_name(), $this->proxy->getName());
        $this->proxy->setName('foo');
        $this->assertEquals('foo', $this->proxy->getName());
        $this->assertEquals(session_name(), $this->proxy->getName());
    }
    public function testNameException()
    {
        session_start();
        $this->proxy->setName('foo');
    }
    public function testId()
    {
        $this->assertEquals(session_id(), $this->proxy->getId());
        $this->proxy->setId('foo');
        $this->assertEquals('foo', $this->proxy->getId());
        $this->assertEquals(session_id(), $this->proxy->getId());
    }
    public function testIdException()
    {
        session_start();
        $this->proxy->setId('foo');
    }
}
