<?php
namespace Symfony\Component\HttpKernel\DependencyInjection;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
class LazyLoadingFragmentHandler extends FragmentHandler
{
    private $container;
    private $initialized = [];
    public function __construct(ContainerInterface $container, RequestStack $requestStack, bool $debug = false)
    {
        $this->container = $container;
        parent::__construct($requestStack, [], $debug);
    }
    public function render($uri, $renderer = 'inline', array $options = [])
    {
        if (!isset($this->initialized[$renderer]) && $this->container->has($renderer)) {
            $this->addRenderer($this->container->get($renderer));
            $this->initialized[$renderer] = true;
        }
        return parent::render($uri, $renderer, $options);
    }
}
