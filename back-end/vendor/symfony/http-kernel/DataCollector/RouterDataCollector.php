<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
class RouterDataCollector extends DataCollector
{
    protected $controllers;
    public function __construct()
    {
        $this->reset();
    }
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if ($response instanceof RedirectResponse) {
            $this->data['redirect'] = true;
            $this->data['url'] = $response->getTargetUrl();
            if ($this->controllers->contains($request)) {
                $this->data['route'] = $this->guessRoute($request, $this->controllers[$request]);
            }
        }
        unset($this->controllers[$request]);
    }
    public function reset()
    {
        $this->controllers = new \SplObjectStorage();
        $this->data = [
            'redirect' => false,
            'url' => null,
            'route' => null,
        ];
    }
    protected function guessRoute(Request $request, $controller)
    {
        return 'n/a';
    }
    public function onKernelController(FilterControllerEvent $event)
    {
        $this->controllers[$event->getRequest()] = $event->getController();
    }
    public function getRedirect()
    {
        return $this->data['redirect'];
    }
    public function getTargetUrl()
    {
        return $this->data['url'];
    }
    public function getTargetRoute()
    {
        return $this->data['route'];
    }
    public function getName()
    {
        return 'router';
    }
}
