<?php
namespace Laravel\Tinker;
use Psy\Shell;
use Illuminate\Support\Str;
class ClassAliasAutoloader
{
    protected $shell;
    protected $classes = [];
    public static function register(Shell $shell, $classMapPath)
    {
        return tap(new static($shell, $classMapPath), function ($loader) {
            spl_autoload_register([$loader, 'aliasClass']);
        });
    }
    public function __construct(Shell $shell, $classMapPath)
    {
        $this->shell = $shell;
        $vendorPath = dirname(dirname($classMapPath));
        $classes = require $classMapPath;
        $excludedAliases = collect(config('tinker.dont_alias', []));
        foreach ($classes as $class => $path) {
            if (! Str::contains($class, '\\') || Str::startsWith($path, $vendorPath)) {
                continue;
            }
            if (! $excludedAliases->filter(function ($alias) use ($class) {
                return Str::startsWith($class, $alias);
            })->isEmpty()) {
                continue;
            }
            $name = class_basename($class);
            if (! isset($this->classes[$name])) {
                $this->classes[$name] = $class;
            }
        }
    }
    public function aliasClass($class)
    {
        if (Str::contains($class, '\\')) {
            return;
        }
        $fullName = isset($this->classes[$class])
            ? $this->classes[$class]
            : false;
        if ($fullName) {
            $this->shell->writeStdout("[!] Aliasing '{$class}' to '{$fullName}' for this Tinker session.\n");
            class_alias($fullName, $class);
        }
    }
    public function unregister()
    {
        spl_autoload_unregister([$this, 'aliasClass']);
    }
    public function __destruct()
    {
        $this->unregister();
    }
}
