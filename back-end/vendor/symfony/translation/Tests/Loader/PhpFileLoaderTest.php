<?php
namespace Symfony\Component\Translation\Tests\Loader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Translation\Loader\PhpFileLoader;
class PhpFileLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new PhpFileLoader();
        $resource = __DIR__.'/../fixtures/resources.php';
        $catalogue = $loader->load($resource, 'en', 'domain1');
        $this->assertEquals(['foo' => 'bar'], $catalogue->all('domain1'));
        $this->assertEquals('en', $catalogue->getLocale());
        $this->assertEquals([new FileResource($resource)], $catalogue->getResources());
    }
    public function testLoadNonExistingResource()
    {
        $loader = new PhpFileLoader();
        $resource = __DIR__.'/../fixtures/non-existing.php';
        $loader->load($resource, 'en', 'domain1');
    }
    public function testLoadThrowsAnExceptionIfFileNotLocal()
    {
        $loader = new PhpFileLoader();
        $resource = 'http:
        $loader->load($resource, 'en', 'domain1');
    }
}
