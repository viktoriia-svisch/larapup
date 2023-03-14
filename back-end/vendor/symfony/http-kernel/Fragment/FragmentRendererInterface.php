<?php
namespace Symfony\Component\HttpKernel\Fragment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
interface FragmentRendererInterface
{
    public function render($uri, Request $request, array $options = []);
    public function getName();
}
