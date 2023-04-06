<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
abstract class AbstractSessionListener implements EventSubscriberInterface
{
    const NO_AUTO_CACHE_CONTROL_HEADER = 'Symfony-Session-NoAutoCacheControl';
    protected $container;
    private $sessionUsageStack = [];
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $session = null;
        $request = $event->getRequest();
        if ($request->hasSession()) {
        } elseif (method_exists($request, 'setSessionFactory')) {
            $request->setSessionFactory(function () { return $this->getSession(); });
        } elseif ($session = $this->getSession()) {
            $request->setSession($session);
        }
        $session = $session ?? ($this->container && $this->container->has('initialized_session') ? $this->container->get('initialized_session') : null);
        $this->sessionUsageStack[] = $session instanceof Session ? $session->getUsageIndex() : 0;
    }
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $response = $event->getResponse();
        $autoCacheControl = !$response->headers->has(self::NO_AUTO_CACHE_CONTROL_HEADER);
        $response->headers->remove(self::NO_AUTO_CACHE_CONTROL_HEADER);
        if (!$session = $this->container && $this->container->has('initialized_session') ? $this->container->get('initialized_session') : $event->getRequest()->getSession()) {
            return;
        }
        if ($session instanceof Session ? $session->getUsageIndex() !== end($this->sessionUsageStack) : $session->isStarted()) {
            if ($autoCacheControl) {
                $response
                    ->setPrivate()
                    ->setMaxAge(0)
                    ->headers->addCacheControlDirective('must-revalidate');
            }
        }
        if ($session->isStarted()) {
            $session->save();
        }
    }
    public function onFinishRequest(FinishRequestEvent $event)
    {
        if ($event->isMasterRequest()) {
            array_pop($this->sessionUsageStack);
        }
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 128],
            KernelEvents::RESPONSE => ['onKernelResponse', -1000],
            KernelEvents::FINISH_REQUEST => ['onFinishRequest'],
        ];
    }
    abstract protected function getSession();
}
