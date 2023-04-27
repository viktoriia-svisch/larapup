<?php
namespace Symfony\Component\Translation\Tests\Loader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\QtFileLoader;
class QtFileLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/resources.ts';
        $catalogue = $loader->load($resource, 'en', 'resources');
        $this->assertEquals(['foo' => 'bar'], $catalogue->all('resources'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals([new FileResource($resource)], $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/non-existing.ts';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadNonLocalResource()
    {
        $loader = new QtFileLoader();
        $resource = 'http:
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadInvalidResource()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/invalid-xml-resources.xlf';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadEmptyResource()
    {
        $loader = new QtFileLoader();
        $resource = __DIR__.'/../fixtures/empty.xlf';
        if (method_exists($this, 'expectException')) {
            $this->expectException('Symfony\Component\Translation\Exception\InvalidResourceException');
            $this->expectExceptionMessage(sprintf('Unable to load "%s".', $resource));
        } else {
            $this->setExpectedException('Symfony\Component\Translation\Exception\InvalidResourceException', sprintf('Unable to load "%s".', $resource));
        }
        $loader->load($resource, 'en', 'domain1');
    }
}
