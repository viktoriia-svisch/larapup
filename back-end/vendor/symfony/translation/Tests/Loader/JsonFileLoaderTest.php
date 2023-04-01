<?php
namespace Symfony\Component\Translation\Tests\Loader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\JsonFileLoader;
class JsonFileLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new JsonFileLoader();
        $resource = __DIR__.'/../fixtures/resources.json';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals(['foo' => 'bar'], $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals([new FileResource($resource)], $catalogue->getResources());
    }
    public function testLoadDoesNothingIfEmpty()
    {
        $loader = new JsonFileLoader();
        $resource = __DIR__.'/../fixtures/empty.json';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals([], $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals([new FileResource($resource)], $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new JsonFileLoader();
        $resource = __DIR__.'/../fixtures/non-existing.json';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testParseException()
    {
        $loader = new JsonFileLoader();
        $resource = __DIR__.'/../fixtures/malformed.json';
        $loader->load($resource, 'en', 'domain1');
    }
}
