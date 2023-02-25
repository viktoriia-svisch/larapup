<?php
namespace Symfony\Component\HttpKernel\Fragment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpCache\SubRequestHandler;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
class InlineFragmentRenderer extends RoutableFragmentRenderer
{
    private $kernel;
    private $dispatcher;
    public function __construct(HttpKernelInterface $kernel, EventDispatcherInterface $dispatcher = null)
    {
        $this->kernel = $kernel;
        $this->dispatcher = $dispatcher;
    }
    public function render($uri, Request $request, array $options = [])
    {
        $reference = null;
        if ($uri instanceof ControllerReference) {
            $reference = $uri;
            $attributes = $reference->attributes;
            $reference->attributes = [];
            foreach (['_format', '_locale'] as $key) {
                if (isset($attributes[$key])) {
                    $reference->attributes[$key] = $attributes[$key];
                }
            }
            $uri = $this->generateFragmentUri($uri, $request, false, false);
            $reference->attributes = array_merge($attributes, $reference->attributes);
        }
        $subRequest = $this->createSubRequest($uri, $request);
        if (null !== $reference) {
            $subRequest->attributes->add($reference->attributes);
        }
        $level = ob_get_level();
        try {
            return SubRequestHandler::handle($this->kernel, $subRequest, HttpKernelInterface::SUB_REQUEST, false);
        } catch (\Exception $e) {
            if (isset($options['ignore_errors']) && $options['ignore_errors'] && $this->dispatcher) {
                $event = new GetResponseForExceptionEvent($this->kernel, $request, HttpKernelInterface::SUB_REQUEST, $e);
                $this->dispatcher->dispatch(KernelEvents::EXCEPTION, $event);
            }
            Response::closeOutputBuffers($level, false);
            if (isset($options['alt'])) {
                $alt = $options['alt'];
                unset($options['alt']);
                return $this->render($alt, $request, $options);
            }
            if (!isset($options['ignore_errors']) || !$options['ignore_errors']) {
                throw $e;
            }
            return new Response();
        }
    }
    protected function createSubRequest($uri, Request $request)
    {
        $cookies = $request->cookies->all();
        $server = $request->server->all();
        unset($server['HTTP_IF_MODIFIED_SINCE']);
        unset($server['HTTP_IF_NONE_MATCH']);
        $subRequest = Request::create($uri, 'get', [], $cookies, [], $server);
        if ($request->headers->has('Surrogate-Capability')) {
            $subRequest->headers->set('Surrogate-Capability', $request->headers->get('Surrogate-Capability'));
        }
        static $setSession;
        if (null === $setSession) {
            $setSession = \Closure::bind(function ($subRequest, $request) { $subRequest->session = $request->session; }, null, Request::class);
        }
        $setSession($subRequest, $request);
        if ($request->get('_format')) {
            $subRequest->attributes->set('_format', $request->get('_format'));
        }
        if ($request->getDefaultLocale() !== $request->getLocale()) {
            $subRequest->setLocale($request->getLocale());
        }
        return $subRequest;
    }
    public function getName()
    {
        return 'inline';
    }
}
