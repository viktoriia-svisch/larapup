<?php
namespace Illuminate\Support;
class NamespacedItemResolver
{
    protected $parsed = [];
    public function parseKey($key)
    {
        if (isset($this->parsed[$key])) {
            return $this->parsed[$key];
        }
        if (strpos($key, '::') === false) {
            $segments = explode('.', $key);
            $parsed = $this->parseBasicSegments($segments);
        } else {
            $parsed = $this->parseNamespacedSegments($key);
        }
        return $this->parsed[$key] = $parsed;
    }
    protected function parseBasicSegments(array $segments)
    {
        $group = $segments[0];
        $item = count($segments) === 1
                    ? null
                    : implode('.', array_slice($segments, 1));
        return [null, $group, $item];
    }
    protected function parseNamespacedSegments($key)
    {
        [$namespace, $item] = explode('::', $key);
        $itemSegments = explode('.', $item);
        $groupAndItem = array_slice(
            $this->parseBasicSegments($itemSegments), 1
        );
        return array_merge([$namespace], $groupAndItem);
    }
    public function setParsedKey($key, $parsed)
    {
        $this->parsed[$key] = $parsed;
    }
}
