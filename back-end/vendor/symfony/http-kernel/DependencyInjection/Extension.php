<?php
namespace Symfony\Component\HttpKernel\DependencyInjection;
use Symfony\Component\DependencyInjection\Extension\Extension as BaseExtension;
abstract class Extension extends BaseExtension
{
    private $annotatedClasses = [];
    public function getAnnotatedClassesToCompile()
    {
        return $this->annotatedClasses;
    }
    public function addAnnotatedClassesToCompile(array $annotatedClasses)
    {
        $this->annotatedClasses = array_merge($this->annotatedClasses, $annotatedClasses);
    }
}
