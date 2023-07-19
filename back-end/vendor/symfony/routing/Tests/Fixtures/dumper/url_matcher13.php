<?php
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherTrait;
use Symfony\Component\Routing\RequestContext;
class ProjectUrlMatcher extends Symfony\Component\Routing\Matcher\UrlMatcher
{
    use PhpMatcherTrait;
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
        $this->matchHost = true;
        $this->regexpList = [
            0 => '{^(?'
                .'|(?i:([^\\.]++)\\.exampple\\.com)\\.(?'
                    .'|/abc([^/]++)(?'
                        .'|(*:56)'
                    .')'
                .')'
                .')/?$}sD',
        ];
        $this->dynamicRoutes = [
            56 => [
                [['_route' => 'r1'], ['foo', 'foo'], null, null, false, true, null],
                [['_route' => 'r2'], ['foo', 'foo'], null, null, false, true, null],
            ],
        ];
    }
}