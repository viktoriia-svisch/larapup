<?php
namespace Illuminate\Translation;
use Illuminate\Contracts\Translation\Loader;
class ArrayLoader implements Loader
{
    protected $messages = [];
    public function load($locale, $group, $namespace = null)
    {
        $namespace = $namespace ?: '*';
        if (isset($this->messages[$namespace][$locale][$group])) {
            return $this->messages[$namespace][$locale][$group];
        }
        return [];
    }
    public function addNamespace($namespace, $hint)
    {
    }
    public function addJsonPath($path)
    {
    }
    public function addMessages($locale, $group, array $messages, $namespace = null)
    {
        $namespace = $namespace ?: '*';
        $this->messages[$namespace][$locale][$group] = $messages;
        return $this;
    }
    public function namespaces()
    {
        return [];
    }
}
