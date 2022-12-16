<?php
namespace Symfony\Component\Routing\Tests\Matcher;
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
class DumpedUrlMatcherTest extends UrlMatcherTest
{
    protected function getUrlMatcher(RouteCollection $routes, RequestContext $context = null)
    {
        static $i = 0;
        $class = 'DumpedUrlMatcher'.++$i;
        $dumper = new PhpMatcherDumper($routes);
        eval('?>'.$dumper->dump(['class' => $class]));
        return new $class($context ?: new RequestContext());
    }
}
