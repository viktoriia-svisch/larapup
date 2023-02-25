<?php
namespace Symfony\Component\VarDumper\Dumper\ContextProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\VarDumper\Cloner\VarCloner;
final class RequestContextProvider implements ContextProviderInterface
{
    private $requestStack;
    private $cloner;
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->cloner = new VarCloner();
        $this->cloner->setMaxItems(0);
    }
    public function getContext(): ?array
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return null;
        }
        $controller = $request->attributes->get('_controller');
        return [
            'uri' => $request->getUri(),
            'method' => $request->getMethod(),
            'controller' => $controller ? $this->cloner->cloneVar($controller) : $controller,
            'identifier' => spl_object_hash($request),
        ];
    }
}
