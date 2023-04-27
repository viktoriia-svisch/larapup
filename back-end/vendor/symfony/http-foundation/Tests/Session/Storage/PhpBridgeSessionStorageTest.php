<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Storage\PhpBridgeSessionStorage;
class PhpBridgeSessionStorageTest extends TestCase
{
    private $savePath;
    protected function setUp()
    {
        $this->iniSet('session.save_handler', 'files');
        $this->iniSet('session.save_path', $this->savePath = sys_get_temp_dir().'/sftest');
        if (!is_dir($this->savePath)) {
            mkdir($this->savePath);
        }
    }
    protected function tearDown()
    {
        session_write_close();
        array_map('unlink', glob($this->savePath.'
    protected function getStorage()
    {
        $storage = new PhpBridgeSessionStorage();
        $storage->registerBag(new AttributeBag());
        return $storage;
    }
    public function testPhpSession()
    {
        $storage = $this->getStorage();
        $this->assertNotSame(\PHP_SESSION_ACTIVE, session_status());
        $this->assertFalse($storage->isStarted());
        session_start();
        $this->assertTrue(isset($_SESSION));
        $this->assertSame(\PHP_SESSION_ACTIVE, session_status());
        $this->assertFalse($storage->isStarted());
        $key = $storage->getMetadataBag()->getStorageKey();
        $this->assertArrayNotHasKey($key, $_SESSION);
        $storage->start();
        $this->assertArrayHasKey($key, $_SESSION);
    }
    public function testClear()
    {
        $storage = $this->getStorage();
        session_start();
        $_SESSION['drak'] = 'loves symfony';
        $storage->getBag('attributes')->set('symfony', 'greatness');
        $key = $storage->getBag('attributes')->getStorageKey();
        $this->assertEquals($_SESSION[$key], ['symfony' => 'greatness']);
        $this->assertEquals($_SESSION['drak'], 'loves symfony');
        $storage->clear();
        $this->assertEquals($_SESSION[$key], []);
        $this->assertEquals($_SESSION['drak'], 'loves symfony');
    }
}
