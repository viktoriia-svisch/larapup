<?php
namespace Symfony\Component\Routing\Tests\Fixtures\AnnotationFixtures;
use Symfony\Component\Routing\Annotation\Route;
class DefaultValueController
{
    public function action($default = 'value')
    {
    }
}
