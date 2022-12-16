<?php
namespace Symfony\Component\HttpKernel\EventListener;
use Psr\Container\ContainerInterface;
class TestSessionListener extends AbstractTestSessionListener
{
    private $container;
    public function __construct(ContainerInterface $container, array $sessionOptions = [])
    {
        $this->container = $container;
        parent::__construct($sessionOptions);
    }
    protected function getSession()
    {
        if (!$this->container->has('session')) {
            return;
        }
        return $this->container->get('session');
    }
}
