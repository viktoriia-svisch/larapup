<?php
namespace Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class FilterControllerArgumentsEvent extends FilterControllerEvent
{
    private $arguments;
    public function __construct(HttpKernelInterface $kernel, callable $controller, array $arguments, Request $request, ?int $requestType)
    {
        parent::__construct($kernel, $controller, $request, $requestType);
        $this->arguments = $arguments;
    }
    public function getArguments()
    {
        return $this->arguments;
    }
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }
}
