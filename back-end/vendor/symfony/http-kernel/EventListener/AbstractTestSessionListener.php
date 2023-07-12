<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
abstract class AbstractTestSessionListener implements EventSubscriberInterface
{
    private $sessionId;
    private $sessionOptions;
    public function __construct(array $sessionOptions = [])
    {
        $this->sessionOptions = $sessionOptions;
    }
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $session = $this->getSession();
        if (!$session) {
            return;
        }
        $cookies = $event->getRequest()->cookies;
        if ($cookies->has($session->getName())) {
            $this->sessionId = $cookies->get($session->getName());
            $session->setId($this->sessionId);
        }
    }
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }
        $session = $request->getSession();
        if ($wasStarted = $session->isStarted()) {
            $session->save();
        }
        if ($session instanceof Session ? !$session->isEmpty() || (null !== $this->sessionId && $session->getId() !== $this->sessionId) : $wasStarted) {
            $params = session_get_cookie_params() + ['samesite' => null];
            foreach ($this->sessionOptions as $k => $v) {
                if (0 === strpos($k, 'cookie_')) {
                    $params[substr($k, 7)] = $v;
                }
            }
            foreach ($event->getResponse()->headers->getCookies() as $cookie) {
                if ($session->getName() === $cookie->getName() && $params['path'] === $cookie->getPath() && $params['domain'] == $cookie->getDomain()) {
                    return;
                }
            }
            $event->getResponse()->headers->setCookie(new Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly'], false, $params['samesite'] ?: null));
            $this->sessionId = $session->getId();
        }
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 192],
            KernelEvents::RESPONSE => ['onKernelResponse', -128],
        ];
    }
    abstract protected function getSession();
}
