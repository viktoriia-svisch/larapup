<?php
namespace Symfony\Component\Translation\Tests\Loader;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Translation\Loader\IcuResFileLoader;
class IcuResFileLoaderTest extends LocalizedTestCase
{
    public function testLoad()
    {
        $loader = new IcuResFileLoader();
        $resource = __DIR__.'/../fixtures/resourcebundle/res';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals(['foo' => 'bar'], $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals([new DirectoryResource($resource)], $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new IcuResFileLoader();
        $loader->load(__DIR__.'/../fixtures/non-existing.txt', 'en', 'domain1');
    }
    public function testLoadInvalidResource()
    {
        $loader = new IcuResFileLoader();
        $loader->load(__DIR__.'/../fixtures/resourcebundle/corrupted', 'en', 'domain1');
    }
}
