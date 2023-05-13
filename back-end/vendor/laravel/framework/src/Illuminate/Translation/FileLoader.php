<?php
namespace Illuminate\Translation;
use RuntimeException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Contracts\Translation\Loader;
class FileLoader implements Loader
{
    protected $files;
    protected $path;
    protected $jsonPaths = [];
    protected $hints = [];
    public function __construct(Filesystem $files, $path)
    {
        $this->path = $path;
        $this->files = $files;
    }
    public function load($locale, $group, $namespace = null)
    {
        if ($group === '*' && $namespace === '*') {
            return $this->loadJsonPaths($locale);
        }
        if (is_null($namespace) || $namespace === '*') {
            return $this->loadPath($this->path, $locale, $group);
        }
        return $this->loadNamespaced($locale, $group, $namespace);
    }
    protected function loadNamespaced($locale, $group, $namespace)
    {
        if (isset($this->hints[$namespace])) {
            $lines = $this->loadPath($this->hints[$namespace], $locale, $group);
            return $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);
        }
        return [];
    }
    protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
    {
        $file = "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.php";
        if ($this->files->exists($file)) {
            return array_replace_recursive($lines, $this->files->getRequire($file));
        }
        return $lines;
    }
    protected function loadPath($path, $locale, $group)
    {
        if ($this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
            return $this->files->getRequire($full);
        }
        return [];
    }
    protected function loadJsonPaths($locale)
    {
        return collect(array_merge($this->jsonPaths, [$this->path]))
            ->reduce(function ($output, $path) use ($locale) {
                if ($this->files->exists($full = "{$path}/{$locale}.json")) {
                    $decoded = json_decode($this->files->get($full), true);
                    if (is_null($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                        throw new RuntimeException("Translation file [{$full}] contains an invalid JSON structure.");
                    }
                    $output = array_merge($output, $decoded);
                }
                return $output;
            }, []);
    }
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;
    }
    public function addJsonPath($path)
    {
        $this->jsonPaths[] = $path;
    }
    public function namespaces()
    {
        return $this->hints;
    }
}
