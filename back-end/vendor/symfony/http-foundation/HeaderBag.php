<?php
namespace Symfony\Component\HttpFoundation;
class HeaderBag implements \IteratorAggregate, \Countable
{
    protected $headers = [];
    protected $cacheControl = [];
    public function __construct(array $headers = [])
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }
    public function __toString()
    {
        if (!$headers = $this->all()) {
            return '';
        }
        ksort($headers);
        $max = max(array_map('strlen', array_keys($headers))) + 1;
        $content = '';
        foreach ($headers as $name => $values) {
            $name = ucwords($name, '-');
            foreach ($values as $value) {
                $content .= sprintf("%-{$max}s %s\r\n", $name.':', $value);
            }
        }
        return $content;
    }
    public function all()
    {
        return $this->headers;
    }
    public function keys()
    {
        return array_keys($this->all());
    }
    public function replace(array $headers = [])
    {
        $this->headers = [];
        $this->add($headers);
    }
    public function add(array $headers)
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }
    public function get($key, $default = null, $first = true)
    {
        $key = str_replace('_', '-', strtolower($key));
        $headers = $this->all();
        if (!\array_key_exists($key, $headers)) {
            if (null === $default) {
                return $first ? null : [];
            }
            return $first ? $default : [$default];
        }
        if ($first) {
            return \count($headers[$key]) ? $headers[$key][0] : $default;
        }
        return $headers[$key];
    }
    public function set($key, $values, $replace = true)
    {
        $key = str_replace('_', '-', strtolower($key));
        if (\is_array($values)) {
            $values = array_values($values);
            if (true === $replace || !isset($this->headers[$key])) {
                $this->headers[$key] = $values;
            } else {
                $this->headers[$key] = array_merge($this->headers[$key], $values);
            }
        } else {
            if (true === $replace || !isset($this->headers[$key])) {
                $this->headers[$key] = [$values];
            } else {
                $this->headers[$key][] = $values;
            }
        }
        if ('cache-control' === $key) {
            $this->cacheControl = $this->parseCacheControl(implode(', ', $this->headers[$key]));
        }
    }
    public function has($key)
    {
        return \array_key_exists(str_replace('_', '-', strtolower($key)), $this->all());
    }
    public function contains($key, $value)
    {
        return \in_array($value, $this->get($key, null, false));
    }
    public function remove($key)
    {
        $key = str_replace('_', '-', strtolower($key));
        unset($this->headers[$key]);
        if ('cache-control' === $key) {
            $this->cacheControl = [];
        }
    }
    public function getDate($key, \DateTime $default = null)
    {
        if (null === $value = $this->get($key)) {
            return $default;
        }
        if (false === $date = \DateTime::createFromFormat(DATE_RFC2822, $value)) {
            throw new \RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value));
        }
        return $date;
    }
    public function addCacheControlDirective($key, $value = true)
    {
        $this->cacheControl[$key] = $value;
        $this->set('Cache-Control', $this->getCacheControlHeader());
    }
    public function hasCacheControlDirective($key)
    {
        return \array_key_exists($key, $this->cacheControl);
    }
    public function getCacheControlDirective($key)
    {
        return \array_key_exists($key, $this->cacheControl) ? $this->cacheControl[$key] : null;
    }
    public function removeCacheControlDirective($key)
    {
        unset($this->cacheControl[$key]);
        $this->set('Cache-Control', $this->getCacheControlHeader());
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->headers);
    }
    public function count()
    {
        return \count($this->headers);
    }
    protected function getCacheControlHeader()
    {
        ksort($this->cacheControl);
        return HeaderUtils::toString($this->cacheControl, ',');
    }
    protected function parseCacheControl($header)
    {
        $parts = HeaderUtils::split($header, ',=');
        return HeaderUtils::combine($parts);
    }
}
