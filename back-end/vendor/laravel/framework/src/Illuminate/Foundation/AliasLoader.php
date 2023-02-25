<?php
namespace Illuminate\Foundation;
class AliasLoader
{
    protected $aliases;
    protected $registered = false;
    protected static $facadeNamespace = 'Facades\\';
    protected static $instance;
    private function __construct($aliases)
    {
        $this->aliases = $aliases;
    }
    public static function getInstance(array $aliases = [])
    {
        if (is_null(static::$instance)) {
            return static::$instance = new static($aliases);
        }
        $aliases = array_merge(static::$instance->getAliases(), $aliases);
        static::$instance->setAliases($aliases);
        return static::$instance;
    }
    public function load($alias)
    {
        if (static::$facadeNamespace && strpos($alias, static::$facadeNamespace) === 0) {
            $this->loadFacade($alias);
            return true;
        }
        if (isset($this->aliases[$alias])) {
            return class_alias($this->aliases[$alias], $alias);
        }
    }
    protected function loadFacade($alias)
    {
        require $this->ensureFacadeExists($alias);
    }
    protected function ensureFacadeExists($alias)
    {
        if (file_exists($path = storage_path('framework/cache/facade-'.sha1($alias).'.php'))) {
            return $path;
        }
        file_put_contents($path, $this->formatFacadeStub(
            $alias, file_get_contents(__DIR__.'/stubs/facade.stub')
        ));
        return $path;
    }
    protected function formatFacadeStub($alias, $stub)
    {
        $replacements = [
            str_replace('/', '\\', dirname(str_replace('\\', '/', $alias))),
            class_basename($alias),
            substr($alias, strlen(static::$facadeNamespace)),
        ];
        return str_replace(
            ['DummyNamespace', 'DummyClass', 'DummyTarget'], $replacements, $stub
        );
    }
    public function alias($class, $alias)
    {
        $this->aliases[$class] = $alias;
    }
    public function register()
    {
        if (! $this->registered) {
            $this->prependToLoaderStack();
            $this->registered = true;
        }
    }
    protected function prependToLoaderStack()
    {
        spl_autoload_register([$this, 'load'], true, true);
    }
    public function getAliases()
    {
        return $this->aliases;
    }
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }
    public function isRegistered()
    {
        return $this->registered;
    }
    public function setRegistered($value)
    {
        $this->registered = $value;
    }
    public static function setFacadeNamespace($namespace)
    {
        static::$facadeNamespace = rtrim($namespace, '\\').'\\';
    }
    public static function setInstance($loader)
    {
        static::$instance = $loader;
    }
    private function __clone()
    {
    }
}
