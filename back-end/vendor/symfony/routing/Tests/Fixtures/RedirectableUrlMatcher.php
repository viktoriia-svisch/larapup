<?php
namespace Symfony\Component\Routing\Tests\Fixtures;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
class RedirectableUrlMatcher extends UrlMatcher implements RedirectableUrlMatcherInterface
{
    public function redirect($path, $route, $scheme = null)
    {
        return [
            '_controller' => 'Some controller reference...',
            'path' => $path,
            'scheme' => $scheme,
        ];
    }
}
