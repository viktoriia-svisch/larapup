<?php
namespace Symfony\Component\HttpKernel\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
interface ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument);
    public function resolve(Request $request, ArgumentMetadata $argument);
}
