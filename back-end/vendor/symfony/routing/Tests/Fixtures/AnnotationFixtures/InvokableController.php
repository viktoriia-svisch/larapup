<?php
namespace Symfony\Component\Routing\Tests\Fixtures\AnnotationFixtures;
use Symfony\Component\Routing\Annotation\Route;
class InvokableController
{
    public function __invoke()
    {
    }
}
