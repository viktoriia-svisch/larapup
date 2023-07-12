<?php
namespace Symfony\Component\Routing\Tests\Loader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Loader\ObjectRouteLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class ObjectRouteLoaderTest extends TestCase
{
    public function testLoadCallsServiceAndReturnsCollectionWithLegacyNotation()
    {
        $loader = new ObjectRouteLoaderForTest();
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));
        $loader->loaderMap = [
            'my_route_provider_service' => new RouteService($collection),
        ];
        $actualRoutes = $loader->load(
            'my_route_provider_service:loadRoutes',
            'service'
        );
        $this->assertSame($collection, $actualRoutes);
        $this->assertNotEmpty($actualRoutes->getResources());
    }
    public function testLoadCallsServiceAndReturnsCollection()
    {
        $loader = new ObjectRouteLoaderForTest();
        $collection = new RouteCollection();
        $collection->add('foo', new Route('/foo'));
        $loader->loaderMap = [
            'my_route_provider_service' => new RouteService($collection),
        ];
        $actualRoutes = $loader->load(
            'my_route_provider_service::loadRoutes',
            'service'
        );
        $this->assertSame($collection, $actualRoutes);
        $this->assertNotEmpty($actualRoutes->getResources());
    }
    public function testExceptionWithoutSyntax($resourceString)
    {
        $loader = new ObjectRouteLoaderForTest();
        $loader->load($resourceString);
    }
    public function getBadResourceStrings()
    {
        return [
            ['Foo'],
            ['Foo:Bar:baz'],
        ];
    }
    public function testExceptionOnNoObjectReturned()
    {
        $loader = new ObjectRouteLoaderForTest();
        $loader->loaderMap = ['my_service' => 'NOT_AN_OBJECT'];
        $loader->load('my_service::method');
    }
    public function testExceptionOnBadMethod()
    {
        $loader = new ObjectRouteLoaderForTest();
        $loader->loaderMap = ['my_service' => new \stdClass()];
        $loader->load('my_service::method');
    }
    public function testExceptionOnMethodNotReturningCollection()
    {
        $service = $this->getMockBuilder('stdClass')
            ->setMethods(['loadRoutes'])
            ->getMock();
        $service->expects($this->once())
            ->method('loadRoutes')
            ->will($this->returnValue('NOT_A_COLLECTION'));
        $loader = new ObjectRouteLoaderForTest();
        $loader->loaderMap = ['my_service' => $service];
        $loader->load('my_service::loadRoutes');
    }
}
class ObjectRouteLoaderForTest extends ObjectRouteLoader
{
    public $loaderMap = [];
    protected function getServiceObject($id)
    {
        return isset($this->loaderMap[$id]) ? $this->loaderMap[$id] : null;
    }
}
class RouteService
{
    private $collection;
    public function __construct($collection)
    {
        $this->collection = $collection;
    }
    public function loadRoutes()
    {
        return $this->collection;
    }
}
