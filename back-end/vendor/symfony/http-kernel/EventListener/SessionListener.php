<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
class SessionListener extends AbstractSessionListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    protected function getSession()
    {
        if (!$this->container->has('session')) {
            return;
        }
        if ($this->container->has('session_storage')
            && ($storage = $this->container->get('session_storage')) instanceof NativeSessionStorage
            && $this->container->get('request_stack')->getMasterRequest()->isSecure()
        ) {
            $storage->setOptions(['cookie_secure' => true]);
        }
        return $this->container->get('session');
    }
}
