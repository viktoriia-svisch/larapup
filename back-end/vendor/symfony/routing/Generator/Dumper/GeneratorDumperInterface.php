<?php
namespace Symfony\Component\Routing\Generator\Dumper;
use Symfony\Component\Routing\RouteCollection;
interface GeneratorDumperInterface
{
    public function dump(array $options = []);
    public function getRoutes();
}
