<?php
use Symfony\Component\Routing\Matcher\Dumper\PhpMatcherTrait;
use Symfony\Component\Routing\RequestContext;
class ProjectUrlMatcher extends Symfony\Component\Routing\Matcher\UrlMatcher
{
    use PhpMatcherTrait;
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }
}
