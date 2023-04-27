<?php
namespace Symfony\Component\Routing\Matcher\Dumper;
use Symfony\Component\Routing\RouteCollection;
class StaticPrefixCollection
{
    private $prefix;
    private $staticPrefixes = [];
    private $prefixes = [];
    private $items = [];
    public function __construct(string $prefix = '/')
    {
        $this->prefix = $prefix;
    }
    public function getPrefix(): string
    {
        return $this->prefix;
    }
    public function getRoutes(): array
    {
        return $this->items;
    }
    public function addRoute(string $prefix, $route)
    {
        list($prefix, $staticPrefix) = $this->getCommonPrefix($prefix, $prefix);
        for ($i = \count($this->items) - 1; 0 <= $i; --$i) {
            $item = $this->items[$i];
            list($commonPrefix, $commonStaticPrefix) = $this->getCommonPrefix($prefix, $this->prefixes[$i]);
            if ($this->prefix === $commonPrefix) {
                if ($this->prefix !== $staticPrefix && $this->prefix !== $this->staticPrefixes[$i]) {
                    continue;
                }
                if ($this->prefix === $staticPrefix && $this->prefix === $this->staticPrefixes[$i]) {
                    break;
                }
                if ($this->prefixes[$i] !== $this->staticPrefixes[$i] && $this->prefix === $this->staticPrefixes[$i]) {
                    break;
                }
                if ($prefix !== $staticPrefix && $this->prefix === $staticPrefix) {
                    break;
                }
                continue;
            }
            if ($item instanceof self && $this->prefixes[$i] === $commonPrefix) {
                $item->addRoute($prefix, $route);
            } else {
                $child = new self($commonPrefix);
                list($child->prefixes[0], $child->staticPrefixes[0]) = $child->getCommonPrefix($this->prefixes[$i], $this->prefixes[$i]);
                list($child->prefixes[1], $child->staticPrefixes[1]) = $child->getCommonPrefix($prefix, $prefix);
                $child->items = [$this->items[$i], $route];
                $this->staticPrefixes[$i] = $commonStaticPrefix;
                $this->prefixes[$i] = $commonPrefix;
                $this->items[$i] = $child;
            }
            return;
        }
        $this->staticPrefixes[] = $staticPrefix;
        $this->prefixes[] = $prefix;
        $this->items[] = $route;
    }
    public function populateCollection(RouteCollection $routes): RouteCollection
    {
        foreach ($this->items as $route) {
            if ($route instanceof self) {
                $route->populateCollection($routes);
            } else {
                $routes->add(...$route);
            }
        }
        return $routes;
    }
    private function getCommonPrefix(string $prefix, string $anotherPrefix): array
    {
        $baseLength = \strlen($this->prefix);
        $end = min(\strlen($prefix), \strlen($anotherPrefix));
        $staticLength = null;
        set_error_handler([__CLASS__, 'handleError']);
        for ($i = $baseLength; $i < $end && $prefix[$i] === $anotherPrefix[$i]; ++$i) {
            if ('(' === $prefix[$i]) {
                $staticLength = $staticLength ?? $i;
                for ($j = 1 + $i, $n = 1; $j < $end && 0 < $n; ++$j) {
                    if ($prefix[$j] !== $anotherPrefix[$j]) {
                        break 2;
                    }
                    if ('(' === $prefix[$j]) {
                        ++$n;
                    } elseif (')' === $prefix[$j]) {
                        --$n;
                    } elseif ('\\' === $prefix[$j] && (++$j === $end || $prefix[$j] !== $anotherPrefix[$j])) {
                        --$j;
                        break;
                    }
                }
                if (0 < $n) {
                    break;
                }
                if (('?' === ($prefix[$j] ?? '') || '?' === ($anotherPrefix[$j] ?? '')) && ($prefix[$j] ?? '') !== ($anotherPrefix[$j] ?? '')) {
                    break;
                }
                $subPattern = substr($prefix, $i, $j - $i);
                if ($prefix !== $anotherPrefix && !preg_match('/^\(\[[^\]]++\]\+\+\)$/', $subPattern) && !preg_match('{(?<!'.$subPattern.')}', '')) {
                    break;
                }
                $i = $j - 1;
            } elseif ('\\' === $prefix[$i] && (++$i === $end || $prefix[$i] !== $anotherPrefix[$i])) {
                --$i;
                break;
            }
        }
        restore_error_handler();
        if ($i < $end && 0b10 === (\ord($prefix[$i]) >> 6) && preg_match('
            do {
                --$i;
            } while (0b10 === (\ord($prefix[$i]) >> 6));
        }
        return [substr($prefix, 0, $i), substr($prefix, 0, $staticLength ?? $i)];
    }
    public static function handleError($type, $msg)
    {
        return 0 === strpos($msg, 'preg_match(): Compilation failed: lookbehind assertion is not fixed length');
    }
}
