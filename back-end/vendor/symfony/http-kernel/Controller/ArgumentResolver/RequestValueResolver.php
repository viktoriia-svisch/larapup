<?php
namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
final class RequestValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Request::class === $argument->getType() || is_subclass_of($argument->getType(), Request::class);
    }
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $request;
    }
}
