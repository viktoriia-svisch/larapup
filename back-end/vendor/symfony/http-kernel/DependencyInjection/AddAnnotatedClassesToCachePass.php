<?php
namespace Symfony\Component\HttpKernel\DependencyInjection;
use Composer\Autoload\ClassLoader;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
class AddAnnotatedClassesToCachePass implements CompilerPassInterface
{
    private $kernel;
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }
    public function process(ContainerBuilder $container)
    {
        $annotatedClasses = $this->kernel->getAnnotatedClassesToCompile();
        foreach ($container->getExtensions() as $extension) {
            if ($extension instanceof Extension) {
                $annotatedClasses = array_merge($annotatedClasses, $extension->getAnnotatedClassesToCompile());
            }
        }
        $existingClasses = $this->getClassesInComposerClassMaps();
        $annotatedClasses = $container->getParameterBag()->resolveValue($annotatedClasses);
        $this->kernel->setAnnotatedClassCache($this->expandClasses($annotatedClasses, $existingClasses));
    }
    private function expandClasses(array $patterns, array $classes)
    {
        $expanded = [];
        foreach ($patterns as $key => $pattern) {
            if ('\\' !== substr($pattern, -1) && false === strpos($pattern, '*')) {
                unset($patterns[$key]);
                $expanded[] = ltrim($pattern, '\\');
            }
        }
        $regexps = $this->patternsToRegexps($patterns);
        foreach ($classes as $class) {
            $class = ltrim($class, '\\');
            if ($this->matchAnyRegexps($class, $regexps)) {
                $expanded[] = $class;
            }
        }
        return array_unique($expanded);
    }
    private function getClassesInComposerClassMaps()
    {
        $classes = [];
        foreach (spl_autoload_functions() as $function) {
            if (!\is_array($function)) {
                continue;
            }
            if ($function[0] instanceof DebugClassLoader) {
                $function = $function[0]->getClassLoader();
            }
            if (\is_array($function) && $function[0] instanceof ClassLoader) {
                $classes += array_filter($function[0]->getClassMap());
            }
        }
        return array_keys($classes);
    }
    private function patternsToRegexps($patterns)
    {
        $regexps = [];
        foreach ($patterns as $pattern) {
            $regex = preg_quote(ltrim($pattern, '\\'));
            $regex = strtr($regex, ['\\*\\*' => '.*?', '\\*' => '[^\\\\]*?']);
            if ('\\' !== substr($regex, -1)) {
                $regex .= '$';
            }
            $regexps[] = '{^\\\\'.$regex.'}';
        }
        return $regexps;
    }
    private function matchAnyRegexps($class, $regexps)
    {
        $blacklisted = false !== strpos($class, 'Test');
        foreach ($regexps as $regex) {
            if ($blacklisted && false === strpos($regex, 'Test')) {
                continue;
            }
            if (preg_match($regex, '\\'.$class)) {
                return true;
            }
        }
        return false;
    }
}
