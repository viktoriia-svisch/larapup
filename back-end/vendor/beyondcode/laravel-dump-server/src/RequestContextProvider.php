<?php
namespace BeyondCode\DumpServer;
use Illuminate\Http\Request;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
class RequestContextProvider implements ContextProviderInterface
{
    private $currentRequest;
    private $cloner;
    public function __construct(Request $currentRequest = null)
    {
        $this->currentRequest = $currentRequest;
        $this->cloner = new VarCloner;
        $this->cloner->setMaxItems(0);
    }
    public function getContext(): ?array
    {
        if ($this->currentRequest === null) {
            return null;
        }
        $controller = null;
        if ($route = $this->currentRequest->route()) {
            $controller = $route->controller;
            if (! $controller && ! is_string($route->action['uses'])) {
                $controller = $route->action['uses'];
            }
        }
        return [
            'uri' => $this->currentRequest->getUri(),
            'method' => $this->currentRequest->getMethod(),
            'controller' => $controller ? $this->cloner->cloneVar(class_basename($controller)) : $controller,
            'identifier' => spl_object_hash($this->currentRequest),
        ];
    }
}
