<?php
namespace Symfony\Component\Routing\Loader\Configurator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class CollectionConfigurator
{
    use Traits\AddTrait;
    use Traits\RouteTrait;
    private $parent;
    private $parentConfigurator;
    private $parentPrefixes;
    public function __construct(RouteCollection $parent, string $name, self $parentConfigurator = null, array $parentPrefixes = null)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->collection = new RouteCollection();
        $this->route = new Route('');
        $this->parentConfigurator = $parentConfigurator; 
        $this->parentPrefixes = $parentPrefixes;
    }
    public function __destruct()
    {
        if (null === $this->prefixes) {
            $this->collection->addPrefix($this->route->getPath());
        }
        $this->parent->addCollection($this->collection);
    }
    final public function collection($name = '')
    {
        return new self($this->collection, $this->name.$name, $this, $this->prefixes);
    }
    final public function prefix($prefix)
    {
        if (\is_array($prefix)) {
            if (null === $this->parentPrefixes) {
            } elseif ($missing = array_diff_key($this->parentPrefixes, $prefix)) {
                throw new \LogicException(sprintf('Collection "%s" is missing prefixes for locale(s) "%s".', $this->name, implode('", "', array_keys($missing))));
            } else {
                foreach ($prefix as $locale => $localePrefix) {
                    if (!isset($this->parentPrefixes[$locale])) {
                        throw new \LogicException(sprintf('Collection "%s" with locale "%s" is missing a corresponding prefix in its parent collection.', $this->name, $locale));
                    }
                    $prefix[$locale] = $this->parentPrefixes[$locale].$localePrefix;
                }
            }
            $this->prefixes = $prefix;
            $this->route->setPath('/');
        } else {
            $this->prefixes = null;
            $this->route->setPath($prefix);
        }
        return $this;
    }
    private function createRoute($path): Route
    {
        return (clone $this->route)->setPath($path);
    }
}
