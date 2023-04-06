<?php
namespace Symfony\Component\HttpKernel\EventListener;
@trigger_error(sprintf('The "%s" class is deprecated since Symfony 4.1, use AbstractSessionListener instead.', SaveSessionListener::class), E_USER_DEPRECATED);
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
class SaveSessionListener implements EventSubscriberInterface
{
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }
        $session = $event->getRequest()->getSession();
        if ($session && $session->isStarted()) {
            $session->save();
        }
    }
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [['onKernelResponse', -1000]],
        ];
    }
}
