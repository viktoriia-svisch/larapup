<?php
namespace Symfony\Component\HttpKernel\Tests\CacheClearer;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;
class Psr6CacheClearerTest extends TestCase
{
    public function testClearPoolsInjectedInConstructor()
    {
        $pool = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $pool
            ->expects($this->once())
            ->method('clear');
        (new Psr6CacheClearer(['pool' => $pool]))->clear('');
    }
    public function testClearPool()
    {
        $pool = $this->getMockBuilder(CacheItemPoolInterface::class)->getMock();
        $pool
            ->expects($this->once())
            ->method('clear');
        (new Psr6CacheClearer(['pool' => $pool]))->clearPool('pool');
    }
    public function testClearPoolThrowsExceptionOnUnreferencedPool()
    {
        (new Psr6CacheClearer())->clearPool('unknown');
    }
}
