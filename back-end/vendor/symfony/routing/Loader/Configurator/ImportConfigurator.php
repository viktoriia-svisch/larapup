<?php
namespace Symfony\Component\Routing\Loader\Configurator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class ImportConfigurator
{
    use Traits\RouteTrait;
    private $parent;
    public function __construct(RouteCollection $parent, RouteCollection $route)
    {
        $this->parent = $parent;
        $this->route = $route;
    }
    public function __destruct()
    {
        $this->parent->addCollection($this->route);
    }
    final public function prefix($prefix, bool $trailingSlashOnRoot = true)
    {
        if (!\is_array($prefix)) {
            $this->route->addPrefix($prefix);
            if (!$trailingSlashOnRoot) {
                $rootPath = (new Route(trim(trim($prefix), '/').'/'))->getPath();
                foreach ($this->route->all() as $route) {
                    if ($route->getPath() === $rootPath) {
                        $route->setPath(rtrim($rootPath, '/'));
                    }
                }
            }
        } else {
            foreach ($prefix as $locale => $localePrefix) {
                $prefix[$locale] = trim(trim($localePrefix), '/');
            }
            foreach ($this->route->all() as $name => $route) {
                if (null === $locale = $route->getDefault('_locale')) {
                    $this->route->remove($name);
                    foreach ($prefix as $locale => $localePrefix) {
                        $localizedRoute = clone $route;
                        $localizedRoute->setDefault('_locale', $locale);
                        $localizedRoute->setDefault('_canonical_route', $name);
                        $localizedRoute->setPath($localePrefix.(!$trailingSlashOnRoot && '/' === $route->getPath() ? '' : $route->getPath()));
                        $this->route->add($name.'.'.$locale, $localizedRoute);
                    }
                } elseif (!isset($prefix[$locale])) {
                    throw new \InvalidArgumentException(sprintf('Route "%s" with locale "%s" is missing a corresponding prefix in its parent collection.', $name, $locale));
                } else {
                    $route->setPath($prefix[$locale].(!$trailingSlashOnRoot && '/' === $route->getPath() ? '' : $route->getPath()));
                    $this->route->add($name, $route);
                }
            }
        }
        return $this;
    }
    final public function namePrefix(string $namePrefix)
    {
        $this->route->addNamePrefix($namePrefix);
        return $this;
    }
}
