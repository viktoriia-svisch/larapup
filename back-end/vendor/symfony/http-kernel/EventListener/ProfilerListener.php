<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;
class ProfilerListener implements EventSubscriberInterface
{
    protected $profiler;
    protected $matcher;
    protected $onlyException;
    protected $onlyMasterRequests;
    protected $exception;
    protected $profiles;
    protected $requestStack;
    protected $parents;
    public function __construct(Profiler $profiler, RequestStack $requestStack, RequestMatcherInterface $matcher = null, bool $onlyException = false, bool $onlyMasterRequests = false)
    {
        $this->profiler = $profiler;
        $this->matcher = $matcher;
        $this->onlyException = $onlyException;
        $this->onlyMasterRequests = $onlyMasterRequests;
        $this->profiles = new \SplObjectStorage();
        $this->parents = new \SplObjectStorage();
        $this->requestStack = $requestStack;
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->onlyMasterRequests && !$event->isMasterRequest()) {
            return;
        }
        $this->exception = $event->getException();
    }
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $master = $event->isMasterRequest();
        if ($this->onlyMasterRequests && !$master) {
            return;
        }
        if ($this->onlyException && null === $this->exception) {
            return;
        }
        $request = $event->getRequest();
        $exception = $this->exception;
        $this->exception = null;
        if (null !== $this->matcher && !$this->matcher->matches($request)) {
            return;
        }
        if (!$profile = $this->profiler->collect($request, $event->getResponse(), $exception)) {
            return;
        }
        $this->profiles[$request] = $profile;
        $this->parents[$request] = $this->requestStack->getParentRequest();
    }
    public function onKernelTerminate(PostResponseEvent $event)
    {
        foreach ($this->profiles as $request) {
            if (null !== $parentRequest = $this->parents[$request]) {
                if (isset($this->profiles[$parentRequest])) {
                    $this->profiles[$parentRequest]->addChild($this->profiles[$request]);
                }
            }
        }
        foreach ($this->profiles as $request) {
            $this->profiler->saveProfile($this->profiles[$request]);
        }
        $this->profiles = new \SplObjectStorage();
        $this->parents = new \SplObjectStorage();
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -100],
            KernelEvents::EXCEPTION => ['onKernelException', 0],
            KernelEvents::TERMINATE => ['onKernelTerminate', -1024],
        ];
    }
}
