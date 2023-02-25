<?php
namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
final class RequestAttributeValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return !$argument->isVariadic() && $request->attributes->has($argument->getName());
    }
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $request->attributes->get($argument->getName());
    }
}
