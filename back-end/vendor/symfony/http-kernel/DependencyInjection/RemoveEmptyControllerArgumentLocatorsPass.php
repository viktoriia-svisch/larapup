<?php
namespace Symfony\Component\HttpKernel\DependencyInjection;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
class RemoveEmptyControllerArgumentLocatorsPass implements CompilerPassInterface
{
    private $controllerLocator;
    public function __construct(string $controllerLocator = 'argument_resolver.controller_locator')
    {
        $this->controllerLocator = $controllerLocator;
    }
    public function process(ContainerBuilder $container)
    {
        $controllerLocator = $container->findDefinition($this->controllerLocator);
        $controllers = $controllerLocator->getArgument(0);
        foreach ($controllers as $controller => $argumentRef) {
            $argumentLocator = $container->getDefinition((string) $argumentRef->getValues()[0]);
            if (!$argumentLocator->getArgument(0)) {
                $reason = sprintf('Removing service-argument resolver for controller "%s": no corresponding services exist for the referenced types.', $controller);
            } else {
                $reason = false;
                list($id, $action) = explode('::', $controller);
                $controllerDef = $container->getDefinition($id);
                foreach ($controllerDef->getMethodCalls() as list($method)) {
                    if (0 === strcasecmp($action, $method)) {
                        $reason = sprintf('Removing method "%s" of service "%s" from controller candidates: the method is called at instantiation, thus cannot be an action.', $action, $id);
                        break;
                    }
                }
                if (!$reason) {
                    $controllers[$id.':'.$action] = $argumentRef;
                    if ('__invoke' === $action) {
                        $controllers[$id] = $argumentRef;
                    }
                    continue;
                }
            }
            unset($controllers[$controller]);
            $container->log($this, $reason);
        }
        $controllerLocator->replaceArgument(0, $controllers);
    }
}
