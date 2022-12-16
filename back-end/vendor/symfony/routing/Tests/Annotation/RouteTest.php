<?php
namespace Symfony\Component\Routing\Tests\Annotation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Annotation\Route;
class RouteTest extends TestCase
{
    public function testInvalidRouteParameter()
    {
        $route = new Route(['foo' => 'bar']);
    }
    public function testTryingToSetLocalesDirectly()
    {
        $route = new Route(['locales' => ['nl' => 'bar']]);
    }
    public function testRouteParameters($parameter, $value, $getter)
    {
        $route = new Route([$parameter => $value]);
        $this->assertEquals($route->$getter(), $value);
    }
    public function getValidParameters()
    {
        return [
            ['value', '/Blog', 'getPath'],
            ['requirements', ['locale' => 'en'], 'getRequirements'],
            ['options', ['compiler_class' => 'RouteCompiler'], 'getOptions'],
            ['name', 'blog_index', 'getName'],
            ['defaults', ['_controller' => 'MyBlogBundle:Blog:index'], 'getDefaults'],
            ['schemes', ['https'], 'getSchemes'],
            ['methods', ['GET', 'POST'], 'getMethods'],
            ['host', '{locale}.example.com', 'getHost'],
            ['condition', 'context.getMethod() == "GET"', 'getCondition'],
            ['value', ['nl' => '/hier', 'en' => '/here'], 'getLocalizedPaths'],
        ];
    }
}
