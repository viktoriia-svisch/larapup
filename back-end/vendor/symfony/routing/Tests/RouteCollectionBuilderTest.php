<?php
namespace Symfony\Component\Routing\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;
class RouteCollectionBuilderTest extends TestCase
{
    public function testImport()
    {
        $resolvedLoader = $this->getMockBuilder('Symfony\Component\Config\Loader\LoaderInterface')->getMock();
        $resolver = $this->getMockBuilder('Symfony\Component\Config\Loader\LoaderResolverInterface')->getMock();
        $resolver->expects($this->once())
            ->method('resolve')
            ->with('admin_routing.yml', 'yaml')
            ->will($this->returnValue($resolvedLoader));
        $originalRoute = new Route('/foo/path');
        $expectedCollection = new RouteCollection();
        $expectedCollection->add('one_test_route', $originalRoute);
        $expectedCollection->addResource(new FileResource(__DIR__.'/Fixtures/file_resource.yml'));
        $resolvedLoader
            ->expects($this->once())
            ->method('load')
            ->with('admin_routing.yml', 'yaml')
            ->will($this->returnValue($expectedCollection));
        $loader = $this->getMockBuilder('Symfony\Component\Config\Loader\LoaderInterface')->getMock();
        $loader->expects($this->any())
            ->method('getResolver')
            ->will($this->returnValue($resolver));
        $routes = new RouteCollectionBuilder($loader);
        $importedRoutes = $routes->import('admin_routing.yml', '/', 'yaml');
        $this->assertInstanceOf('Symfony\Component\Routing\RouteCollectionBuilder', $importedRoutes);
        $addedCollection = $importedRoutes->build();
        $route = $addedCollection->get('one_test_route');
        $this->assertSame($originalRoute, $route);
        $this->assertCount(1, $addedCollection->getResources());
        $routeCollection = $routes->build();
        $this->assertCount(1, $routes->build());
        $this->assertCount(1, $routeCollection->getResources());
    }
    public function testImportAddResources()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder(new YamlFileLoader(new FileLocator([__DIR__.'/Fixtures/'])));
        $routeCollectionBuilder->import('file_resource.yml');
        $routeCollection = $routeCollectionBuilder->build();
        $this->assertCount(1, $routeCollection->getResources());
    }
    public function testImportWithoutLoaderThrowsException()
    {
        $collectionBuilder = new RouteCollectionBuilder();
        $collectionBuilder->import('routing.yml');
    }
    public function testAdd()
    {
        $collectionBuilder = new RouteCollectionBuilder();
        $addedRoute = $collectionBuilder->add('/checkout', 'AppBundle:Order:checkout');
        $addedRoute2 = $collectionBuilder->add('/blogs', 'AppBundle:Blog:list', 'blog_list');
        $this->assertInstanceOf('Symfony\Component\Routing\Route', $addedRoute);
        $this->assertEquals('AppBundle:Order:checkout', $addedRoute->getDefault('_controller'));
        $finalCollection = $collectionBuilder->build();
        $this->assertSame($addedRoute2, $finalCollection->get('blog_list'));
    }
    public function testFlushOrdering()
    {
        $importedCollection = new RouteCollection();
        $importedCollection->add('imported_route1', new Route('/imported/foo1'));
        $importedCollection->add('imported_route2', new Route('/imported/foo2'));
        $loader = $this->getMockBuilder('Symfony\Component\Config\Loader\LoaderInterface')->getMock();
        $loader->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));
        $loader
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($importedCollection));
        $routes = new RouteCollectionBuilder($loader);
        $routes->add('/checkout', 'AppBundle:Order:checkout', 'checkout_route');
        $routes->mount('/', $routes->import('admin_routing.yml'));
        $routes->add('/', 'AppBundle:Default:homepage', 'homepage');
        $routes->add('/admin', 'AppBundle:Admin:dashboard', 'admin_dashboard');
        $routes->setDefault('_locale', 'fr');
        $actualCollection = $routes->build();
        $this->assertCount(5, $actualCollection);
        $actualRouteNames = array_keys($actualCollection->all());
        $this->assertEquals([
            'checkout_route',
            'imported_route1',
            'imported_route2',
            'homepage',
            'admin_dashboard',
        ], $actualRouteNames);
        $checkoutRoute = $actualCollection->get('checkout_route');
        $defaults = $checkoutRoute->getDefaults();
        $this->assertArrayHasKey('_locale', $defaults);
        $this->assertEquals('fr', $defaults['_locale']);
    }
    public function testFlushSetsRouteNames()
    {
        $collectionBuilder = new RouteCollectionBuilder();
        $collectionBuilder->add('/admin', 'AppBundle:Admin:dashboard', 'admin_dashboard');
        $collectionBuilder->add('/blogs', 'AppBundle:Blog:list')
            ->setMethods(['GET']);
        $collectionBuilder->add('/products', 'AppBundle:Product:list', 100);
        $actualCollection = $collectionBuilder->build();
        $actualRouteNames = array_keys($actualCollection->all());
        $this->assertEquals([
            'admin_dashboard',
            'GET_blogs',
            '100',
        ], $actualRouteNames);
    }
    public function testFlushSetsDetailsOnChildrenRoutes()
    {
        $routes = new RouteCollectionBuilder();
        $routes->add('/blogs/{page}', 'listAction', 'blog_list')
            ->setDefault('page', 1)
            ->setRequirement('id', '\d+')
            ->setOption('expose', true)
            ->setDefault('_format', 'html')
            ->setRequirement('_format', 'json|xml')
            ->setOption('fooBar', true)
            ->setHost('example.com')
            ->setCondition('request.isSecure()')
            ->setSchemes(['https'])
            ->setMethods(['POST']);
        $routes->add('/blogs/{id}', 'editAction', 'blog_edit');
        $routes
            ->setDefault('_format', 'json')
            ->setRequirement('_format', 'xml')
            ->setOption('fooBar', false)
            ->setHost('symfony.com')
            ->setCondition('request.query.get("page")==1')
            ->setDefault('_locale', 'fr')
            ->setRequirement('_locale', 'fr|en')
            ->setOption('niceRoute', true)
            ->setSchemes(['http'])
            ->setMethods(['GET', 'POST']);
        $collection = $routes->build();
        $actualListRoute = $collection->get('blog_list');
        $this->assertEquals(1, $actualListRoute->getDefault('page'));
        $this->assertEquals('\d+', $actualListRoute->getRequirement('id'));
        $this->assertTrue($actualListRoute->getOption('expose'));
        $this->assertEquals('html', $actualListRoute->getDefault('_format'));
        $this->assertEquals('json|xml', $actualListRoute->getRequirement('_format'));
        $this->assertTrue($actualListRoute->getOption('fooBar'));
        $this->assertEquals('example.com', $actualListRoute->getHost());
        $this->assertEquals('request.isSecure()', $actualListRoute->getCondition());
        $this->assertEquals(['https'], $actualListRoute->getSchemes());
        $this->assertEquals(['POST'], $actualListRoute->getMethods());
        $this->assertEquals('fr', $actualListRoute->getDefault('_locale'));
        $this->assertEquals('fr|en', $actualListRoute->getRequirement('_locale'));
        $this->assertTrue($actualListRoute->getOption('niceRoute'));
        $actualEditRoute = $collection->get('blog_edit');
        $this->assertEquals('symfony.com', $actualEditRoute->getHost());
        $this->assertEquals('request.query.get("page")==1', $actualEditRoute->getCondition());
        $this->assertEquals(['http'], $actualEditRoute->getSchemes());
        $this->assertEquals(['GET', 'POST'], $actualEditRoute->getMethods());
    }
    public function testFlushPrefixesPaths($collectionPrefix, $routePath, $expectedPath)
    {
        $routes = new RouteCollectionBuilder();
        $routes->add($routePath, 'someController', 'test_route');
        $outerRoutes = new RouteCollectionBuilder();
        $outerRoutes->mount($collectionPrefix, $routes);
        $collection = $outerRoutes->build();
        $this->assertEquals($expectedPath, $collection->get('test_route')->getPath());
    }
    public function providePrefixTests()
    {
        $tests = [];
        $tests[] = ['', '/foo', '/foo'];
        $tests[] = ['/{admin}', '/foo', '/{admin}/foo'];
        $tests[] = ['0', '/foo', '/0/foo'];
        $tests[] = ['/ /', '/foo', '/ /foo'];
        return $tests;
    }
    public function testFlushSetsPrefixedWithMultipleLevels()
    {
        $loader = $this->getMockBuilder('Symfony\Component\Config\Loader\LoaderInterface')->getMock();
        $routes = new RouteCollectionBuilder($loader);
        $routes->add('homepage', 'MainController::homepageAction', 'homepage');
        $adminRoutes = $routes->createBuilder();
        $adminRoutes->add('/dashboard', 'AdminController::dashboardAction', 'admin_dashboard');
        $adminBlogRoutes = $routes->createBuilder();
        $adminBlogRoutes->add('/new', 'BlogController::newAction', 'admin_blog_new');
        $adminRoutes->mount('/blog', $adminBlogRoutes);
        $routes->mount('/admin', $adminRoutes);
        $adminRoutes->add('/users', 'AdminController::userAction', 'admin_users');
        $otherAdminRoutes = $routes->createBuilder();
        $otherAdminRoutes->add('/sales', 'StatsController::indexAction', 'admin_stats_sales');
        $adminRoutes->mount('/stats', $otherAdminRoutes);
        $importedCollection = new RouteCollection();
        $importedCollection->add('imported_route', new Route('/foo'));
        $loader->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));
        $loader
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValue($importedCollection));
        $adminRoutes->import('admin.yml', '/imported');
        $collection = $routes->build();
        $this->assertEquals('/admin/dashboard', $collection->get('admin_dashboard')->getPath(), 'Routes before mounting have the prefix');
        $this->assertEquals('/admin/users', $collection->get('admin_users')->getPath(), 'Routes after mounting have the prefix');
        $this->assertEquals('/admin/blog/new', $collection->get('admin_blog_new')->getPath(), 'Sub-collections receive prefix even if mounted before parent prefix');
        $this->assertEquals('/admin/stats/sales', $collection->get('admin_stats_sales')->getPath(), 'Sub-collections receive prefix if mounted after parent prefix');
        $this->assertEquals('/admin/imported/foo', $collection->get('imported_route')->getPath(), 'Normal RouteCollections are also prefixed properly');
    }
    public function testAutomaticRouteNamesDoNotConflict()
    {
        $routes = new RouteCollectionBuilder();
        $adminRoutes = $routes->createBuilder();
        $adminRoutes->add('/dashboard', '');
        $accountRoutes = $routes->createBuilder();
        $accountRoutes->add('/dashboard', '')
            ->setMethods(['GET']);
        $accountRoutes->add('/dashboard', '')
            ->setMethods(['POST']);
        $routes->mount('/admin', $adminRoutes);
        $routes->mount('/account', $accountRoutes);
        $collection = $routes->build();
        $this->assertCount(3, $collection->all());
    }
    public function testAddsThePrefixOnlyOnceWhenLoadingMultipleCollections()
    {
        $firstCollection = new RouteCollection();
        $firstCollection->add('a', new Route('/a'));
        $secondCollection = new RouteCollection();
        $secondCollection->add('b', new Route('/b'));
        $loader = $this->getMockBuilder('Symfony\Component\Config\Loader\LoaderInterface')->getMock();
        $loader->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));
        $loader
            ->expects($this->any())
            ->method('load')
            ->will($this->returnValue([$firstCollection, $secondCollection]));
        $routeCollectionBuilder = new RouteCollectionBuilder($loader);
        $routeCollectionBuilder->import('/directory/recurse/*', '/other/', 'glob');
        $routes = $routeCollectionBuilder->build()->all();
        $this->assertCount(2, $routes);
        $this->assertEquals('/other/a', $routes['a']->getPath());
        $this->assertEquals('/other/b', $routes['b']->getPath());
    }
}
