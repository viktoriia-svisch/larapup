<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
class MockFileSessionStorageTest extends TestCase
{
    private $sessionDir;
    protected $storage;
    protected function setUp()
    {
        $this->sessionDir = sys_get_temp_dir().'/sftest';
        $this->storage = $this->getStorage();
    }
    protected function tearDown()
    {
        $this->sessionDir = null;
        $this->storage = null;
        array_map('unlink', glob($this->sessionDir.'
    public function testSaveWithoutStart()
    {
        $storage1 = $this->getStorage();
        $storage1->save();
    }
    private function getStorage()
    {
        $storage = new MockFileSessionStorage($this->sessionDir);
        $storage->registerBag(new FlashBag());
        $storage->registerBag(new AttributeBag());
        return $storage;
    }
}
