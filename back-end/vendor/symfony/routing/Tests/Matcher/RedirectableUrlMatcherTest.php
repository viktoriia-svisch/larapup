<?php
namespace Symfony\Component\Routing\Tests\Matcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class RedirectableUrlMatcherTest extends UrlMatcherTest
{
    public function testMissingTrailingSlash()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/'));
        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->will($this->returnValue([]));
        $matcher->match('/foo');
    }
    public function testExtraTrailingSlash()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo'));
        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->will($this->returnValue([]));
        $matcher->match('/foo/');
    }
    public function testRedirectWhenNoSlashForNonSafeMethod()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/'));
        $context = new RequestContext();
        $context->setMethod('POST');
        $matcher = $this->getUrlMatcher($coll, $context);
        $matcher->match('/foo');
    }
    public function testSchemeRedirectRedirectsToFirstScheme()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', [], [], [], '', ['FTP', 'HTTPS']));
        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/foo', 'foo', 'ftp')
            ->will($this->returnValue(['_route' => 'foo']))
        ;
        $matcher->match('/foo');
    }
    public function testNoSchemaRedirectIfOneOfMultipleSchemesMatches()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', [], [], [], '', ['https', 'http']));
        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->never())
            ->method('redirect');
        $matcher->match('/foo');
    }
    public function testSchemeRedirectWithParams()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{bar}', [], [], [], '', ['https']));
        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/foo/baz', 'foo', 'https')
            ->will($this->returnValue(['redirect' => 'value']))
        ;
        $this->assertEquals(['_route' => 'foo', 'bar' => 'baz', 'redirect' => 'value'], $matcher->match('/foo/baz'));
    }
    public function testSchemeRedirectForRoot()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/', [], [], [], '', ['https']));
        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/', 'foo', 'https')
            ->will($this->returnValue(['redirect' => 'value']));
        $this->assertEquals(['_route' => 'foo', 'redirect' => 'value'], $matcher->match('/'));
    }
    public function testSlashRedirectWithParams()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/{bar}/'));
        $matcher = $this->getUrlMatcher($coll);
        $matcher
            ->expects($this->once())
            ->method('redirect')
            ->with('/foo/baz/', 'foo', null)
            ->will($this->returnValue(['redirect' => 'value']))
        ;
        $this->assertEquals(['_route' => 'foo', 'bar' => 'baz', 'redirect' => 'value'], $matcher->match('/foo/baz'));
    }
    public function testRedirectPreservesUrlEncoding()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo:bar/'));
        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->with('/foo%3Abar/')->willReturn([]);
        $matcher->match('/foo%3Abar');
    }
    public function testSchemeRequirement()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo', [], [], [], '', ['https']));
        $matcher = $this->getUrlMatcher($coll, new RequestContext());
        $matcher->expects($this->once())->method('redirect')->with('/foo', 'foo', 'https')->willReturn([]);
        $this->assertSame(['_route' => 'foo'], $matcher->match('/foo'));
    }
    public function testFallbackPage()
    {
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo/'));
        $coll->add('bar', new Route('/{name}'));
        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->with('/foo/', 'foo')->will($this->returnValue(['_route' => 'foo']));
        $this->assertSame(['_route' => 'foo'], $matcher->match('/foo'));
        $coll = new RouteCollection();
        $coll->add('foo', new Route('/foo'));
        $coll->add('bar', new Route('/{name}/'));
        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->with('/foo', 'foo')->will($this->returnValue(['_route' => 'foo']));
        $this->assertSame(['_route' => 'foo'], $matcher->match('/foo/'));
    }
    public function testMissingTrailingSlashAndScheme()
    {
        $coll = new RouteCollection();
        $coll->add('foo', (new Route('/foo/'))->setSchemes(['https']));
        $matcher = $this->getUrlMatcher($coll);
        $matcher->expects($this->once())->method('redirect')->with('/foo/', 'foo', 'https')->will($this->returnValue([]));
        $matcher->match('/foo');
    }
    public function testSlashAndVerbPrecedenceWithRedirection()
    {
        $coll = new RouteCollection();
        $coll->add('a', new Route('/api/customers/{customerId}/contactpersons', [], [], [], '', [], ['post']));
        $coll->add('b', new Route('/api/customers/{customerId}/contactpersons/', [], [], [], '', [], ['get']));
        $matcher = $this->getUrlMatcher($coll);
        $expected = [
            '_route' => 'b',
            'customerId' => '123',
        ];
        $this->assertEquals($expected, $matcher->match('/api/customers/123/contactpersons/'));
        $matcher->expects($this->once())->method('redirect')->with('/api/customers/123/contactpersons/')->willReturn([]);
        $this->assertEquals($expected, $matcher->match('/api/customers/123/contactpersons'));
    }
    protected function getUrlMatcher(RouteCollection $routes, RequestContext $context = null)
    {
        return $this->getMockForAbstractClass('Symfony\Component\Routing\Matcher\RedirectableUrlMatcher', [$routes, $context ?: new RequestContext()]);
    }
}
