<?php
namespace Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
final class SessionValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        if (!$request->hasSession()) {
            return false;
        }
        $type = $argument->getType();
        if (SessionInterface::class !== $type && !is_subclass_of($type, SessionInterface::class)) {
            return false;
        }
        return $request->getSession() instanceof $type;
    }
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield $request->getSession();
    }
}
