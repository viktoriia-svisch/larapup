<?php
namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
final class DefaultValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $argument->hasDefaultValue() || (null !== $argument->getType() && $argument->isNullable() && !$argument->isVariadic());
    }
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $argument->hasDefaultValue() ? $argument->getDefaultValue() : null;
    }
}
