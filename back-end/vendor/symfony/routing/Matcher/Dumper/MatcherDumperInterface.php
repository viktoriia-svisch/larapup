<?php
namespace Symfony\Component\Routing\Matcher\Dumper;
use Symfony\Component\Routing\RouteCollection;
interface MatcherDumperInterface
{
    public function dump(array $options = []);
    public function getRoutes();
}
