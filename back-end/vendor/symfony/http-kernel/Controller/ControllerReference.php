<?php
namespace Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpKernel\Fragment\FragmentRendererInterface;
class ControllerReference
{
    public $controller;
    public $attributes = [];
    public $query = [];
    public function __construct(string $controller, array $attributes = [], array $query = [])
    {
        $this->controller = $controller;
        $this->attributes = $attributes;
        $this->query = $query;
    }
}
