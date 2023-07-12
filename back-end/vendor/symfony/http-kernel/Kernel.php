<?php
namespace Symfony\Component\HttpKernel;
use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\AddAnnotatedClassesToCachePass;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;
abstract class Kernel implements KernelInterface, RebootableInterface, TerminableInterface
{
    protected $bundles = [];
    protected $container;
    protected $rootDir;
    protected $environment;
    protected $debug;
    protected $booted = false;
    protected $name;
    protected $startTime;
    private $projectDir;
    private $warmupDir;
    private $requestStackSize = 0;
    private $resetServices = false;
    const VERSION = '4.2.4';
    const VERSION_ID = 40204;
    const MAJOR_VERSION = 4;
    const MINOR_VERSION = 2;
    const RELEASE_VERSION = 4;
    const EXTRA_VERSION = '';
    const END_OF_MAINTENANCE = '07/2019';
    const END_OF_LIFE = '01/2020';
    public function __construct(string $environment, bool $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
        $this->rootDir = $this->getRootDir(false);
        $this->name = $this->getName(false);
    }
    public function __clone()
    {
        $this->booted = false;
        $this->container = null;
        $this->requestStackSize = 0;
        $this->resetServices = false;
    }
    public function boot()
    {
        if (true === $this->booted) {
            if (!$this->requestStackSize && $this->resetServices) {
                if ($this->container->has('services_resetter')) {
                    $this->container->get('services_resetter')->reset();
                }
                $this->resetServices = false;
                if ($this->debug) {
                    $this->startTime = microtime(true);
                }
            }
            return;
        }
        if ($this->debug) {
            $this->startTime = microtime(true);
        }
        if ($this->debug && !isset($_ENV['SHELL_VERBOSITY']) && !isset($_SERVER['SHELL_VERBOSITY'])) {
            putenv('SHELL_VERBOSITY=3');
            $_ENV['SHELL_VERBOSITY'] = 3;
            $_SERVER['SHELL_VERBOSITY'] = 3;
        }
        $this->initializeBundles();
        $this->initializeContainer();
        foreach ($this->getBundles() as $bundle) {
            $bundle->setContainer($this->container);
            $bundle->boot();
        }
        $this->booted = true;
    }
    public function reboot($warmupDir)
    {
        $this->shutdown();
        $this->warmupDir = $warmupDir;
        $this->boot();
    }
    public function terminate(Request $request, Response $response)
    {
        if (false === $this->booted) {
            return;
        }
        if ($this->getHttpKernel() instanceof TerminableInterface) {
            $this->getHttpKernel()->terminate($request, $response);
        }
    }
    public function shutdown()
    {
        if (false === $this->booted) {
            return;
        }
        $this->booted = false;
        foreach ($this->getBundles() as $bundle) {
            $bundle->shutdown();
            $bundle->setContainer(null);
        }
        $this->container = null;
        $this->requestStackSize = 0;
        $this->resetServices = false;
    }
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->boot();
        ++$this->requestStackSize;
        $this->resetServices = true;
        try {
            return $this->getHttpKernel()->handle($request, $type, $catch);
        } finally {
            --$this->requestStackSize;
        }
    }
    protected function getHttpKernel()
    {
        return $this->container->get('http_kernel');
    }
    public function getBundles()
    {
        return $this->bundles;
    }
    public function getBundle($name)
    {
        if (!isset($this->bundles[$name])) {
            $class = \get_class($this);
            $class = 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class).'@anonymous' : $class;
            throw new \InvalidArgumentException(sprintf('Bundle "%s" does not exist or it is not enabled. Maybe you forgot to add it in the registerBundles() method of your %s.php file?', $name, $class));
        }
        return $this->bundles[$name];
    }
    public function locateResource($name, $dir = null, $first = true)
    {
        if ('@' !== $name[0]) {
            throw new \InvalidArgumentException(sprintf('A resource name must start with @ ("%s" given).', $name));
        }
        if (false !== strpos($name, '..')) {
            throw new \RuntimeException(sprintf('File name "%s" contains invalid characters (..).', $name));
        }
        $bundleName = substr($name, 1);
        $path = '';
        if (false !== strpos($bundleName, '/')) {
            list($bundleName, $path) = explode('/', $bundleName, 2);
        }
        $isResource = 0 === strpos($path, 'Resources') && null !== $dir;
        $overridePath = substr($path, 9);
        $bundle = $this->getBundle($bundleName);
        $files = [];
        if ($isResource && file_exists($file = $dir.'/'.$bundle->getName().$overridePath)) {
            $files[] = $file;
        }
        if (file_exists($file = $bundle->getPath().'/'.$path)) {
            if ($first && !$isResource) {
                return $file;
            }
            $files[] = $file;
        }
        if (\count($files) > 0) {
            return $first && $isResource ? $files[0] : $files;
        }
        throw new \InvalidArgumentException(sprintf('Unable to find file "%s".', $name));
    }
    public function getName()
    {
        if (0 === \func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        }
        if (null === $this->name) {
            $this->name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->rootDir));
            if (ctype_digit($this->name[0])) {
                $this->name = '_'.$this->name;
            }
        }
        return $this->name;
    }
    public function getEnvironment()
    {
        return $this->environment;
    }
    public function isDebug()
    {
        return $this->debug;
    }
    public function getRootDir()
    {
        if (0 === \func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2, use getProjectDir() instead.', __METHOD__), E_USER_DEPRECATED);
        }
        if (null === $this->rootDir) {
            $r = new \ReflectionObject($this);
            $this->rootDir = \dirname($r->getFileName());
        }
        return $this->rootDir;
    }
    public function getProjectDir()
    {
        if (null === $this->projectDir) {
            $r = new \ReflectionObject($this);
            $dir = $rootDir = \dirname($r->getFileName());
            while (!file_exists($dir.'/composer.json')) {
                if ($dir === \dirname($dir)) {
                    return $this->projectDir = $rootDir;
                }
                $dir = \dirname($dir);
            }
            $this->projectDir = $dir;
        }
        return $this->projectDir;
    }
    public function getContainer()
    {
        return $this->container;
    }
    public function setAnnotatedClassCache(array $annotatedClasses)
    {
        file_put_contents(($this->warmupDir ?: $this->getCacheDir()).'/annotations.map', sprintf('<?php return %s;', var_export($annotatedClasses, true)));
    }
    public function getStartTime()
    {
        return $this->debug ? $this->startTime : -INF;
    }
    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }
    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log';
    }
    public function getCharset()
    {
        return 'UTF-8';
    }
    public function getAnnotatedClassesToCompile(): array
    {
        return [];
    }
    protected function initializeBundles()
    {
        $this->bundles = [];
        foreach ($this->registerBundles() as $bundle) {
            $name = $bundle->getName();
            if (isset($this->bundles[$name])) {
                throw new \LogicException(sprintf('Trying to register two bundles with the same name "%s"', $name));
            }
            $this->bundles[$name] = $bundle;
        }
    }
    protected function build(ContainerBuilder $container)
    {
    }
    protected function getContainerClass()
    {
        $class = \get_class($this);
        $class = 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class).str_replace('.', '_', ContainerBuilder::hash($class)) : $class;
        return $this->name.str_replace('\\', '_', $class).ucfirst($this->environment).($this->debug ? 'Debug' : '').'Container';
    }
    protected function getContainerBaseClass()
    {
        return 'Container';
    }
    protected function initializeContainer()
    {
        $class = $this->getContainerClass();
        $cacheDir = $this->warmupDir ?: $this->getCacheDir();
        $cache = new ConfigCache($cacheDir.'/'.$class.'.php', $this->debug);
        $oldContainer = null;
        if ($fresh = $cache->isFresh()) {
            $errorLevel = error_reporting(\E_ALL ^ \E_WARNING);
            $fresh = $oldContainer = false;
            try {
                if (file_exists($cache->getPath()) && \is_object($this->container = include $cache->getPath())) {
                    $this->container->set('kernel', $this);
                    $oldContainer = $this->container;
                    $fresh = true;
                }
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            } finally {
                error_reporting($errorLevel);
            }
        }
        if ($fresh) {
            return;
        }
        if ($this->debug) {
            $collectedLogs = [];
            $previousHandler = \defined('PHPUNIT_COMPOSER_INSTALL');
            $previousHandler = $previousHandler ?: set_error_handler(function ($type, $message, $file, $line) use (&$collectedLogs, &$previousHandler) {
                if (E_USER_DEPRECATED !== $type && E_DEPRECATED !== $type) {
                    return $previousHandler ? $previousHandler($type, $message, $file, $line) : false;
                }
                if (isset($collectedLogs[$message])) {
                    ++$collectedLogs[$message]['count'];
                    return;
                }
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
                for ($i = 0; isset($backtrace[$i]); ++$i) {
                    if (isset($backtrace[$i]['file'], $backtrace[$i]['line']) && $backtrace[$i]['line'] === $line && $backtrace[$i]['file'] === $file) {
                        $backtrace = \array_slice($backtrace, 1 + $i);
                        break;
                    }
                }
                for ($i = \count($backtrace) - 2; 0 < $i; --$i) {
                    if (DebugClassLoader::class === ($backtrace[$i]['class'] ?? null)) {
                        $backtrace = [$backtrace[$i + 1]];
                        break;
                    }
                }
                $collectedLogs[$message] = [
                    'type' => $type,
                    'message' => $message,
                    'file' => $file,
                    'line' => $line,
                    'trace' => [$backtrace[0]],
                    'count' => 1,
                ];
            });
        }
        try {
            $container = null;
            $container = $this->buildContainer();
            $container->compile();
        } finally {
            if ($this->debug && true !== $previousHandler) {
                restore_error_handler();
                file_put_contents($cacheDir.'/'.$class.'Deprecations.log', serialize(array_values($collectedLogs)));
                file_put_contents($cacheDir.'/'.$class.'Compiler.log', null !== $container ? implode("\n", $container->getCompiler()->getLog()) : '');
            }
        }
        if (null === $oldContainer && file_exists($cache->getPath())) {
            $errorLevel = error_reporting(\E_ALL ^ \E_WARNING);
            try {
                $oldContainer = include $cache->getPath();
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            } finally {
                error_reporting($errorLevel);
            }
        }
        $oldContainer = \is_object($oldContainer) ? new \ReflectionClass($oldContainer) : false;
        $this->dumpContainer($cache, $container, $class, $this->getContainerBaseClass());
        $this->container = require $cache->getPath();
        $this->container->set('kernel', $this);
        if ($oldContainer && \get_class($this->container) !== $oldContainer->name) {
            static $legacyContainers = [];
            $oldContainerDir = \dirname($oldContainer->getFileName());
            $legacyContainers[$oldContainerDir.'.legacy'] = true;
            foreach (glob(\dirname($oldContainerDir).\DIRECTORY_SEPARATOR.'*.legacy') as $legacyContainer) {
                if (!isset($legacyContainers[$legacyContainer]) && @unlink($legacyContainer)) {
                    (new Filesystem())->remove(substr($legacyContainer, 0, -7));
                }
            }
            touch($oldContainerDir.'.legacy');
        }
        if ($this->container->has('cache_warmer')) {
            $this->container->get('cache_warmer')->warmUp($this->container->getParameter('kernel.cache_dir'));
        }
    }
    protected function getKernelParameters()
    {
        $bundles = [];
        $bundlesMetadata = [];
        foreach ($this->bundles as $name => $bundle) {
            $bundles[$name] = \get_class($bundle);
            $bundlesMetadata[$name] = [
                'path' => $bundle->getPath(),
                'namespace' => $bundle->getNamespace(),
            ];
        }
        return [
            'kernel.root_dir' => realpath($this->rootDir) ?: $this->rootDir,
            'kernel.project_dir' => realpath($this->getProjectDir()) ?: $this->getProjectDir(),
            'kernel.environment' => $this->environment,
            'kernel.debug' => $this->debug,
            'kernel.name' => $this->name,
            'kernel.cache_dir' => realpath($cacheDir = $this->warmupDir ?: $this->getCacheDir()) ?: $cacheDir,
            'kernel.logs_dir' => realpath($this->getLogDir()) ?: $this->getLogDir(),
            'kernel.bundles' => $bundles,
            'kernel.bundles_metadata' => $bundlesMetadata,
            'kernel.charset' => $this->getCharset(),
            'kernel.container_class' => $this->getContainerClass(),
        ];
    }
    protected function buildContainer()
    {
        foreach (['cache' => $this->warmupDir ?: $this->getCacheDir(), 'logs' => $this->getLogDir()] as $name => $dir) {
            if (!is_dir($dir)) {
                if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (!is_writable($dir)) {
                throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }
        $container = $this->getContainerBuilder();
        $container->addObjectResource($this);
        $this->prepareContainer($container);
        if (null !== $cont = $this->registerContainerConfiguration($this->getContainerLoader($container))) {
            $container->merge($cont);
        }
        $container->addCompilerPass(new AddAnnotatedClassesToCachePass($this));
        return $container;
    }
    protected function prepareContainer(ContainerBuilder $container)
    {
        $extensions = [];
        foreach ($this->bundles as $bundle) {
            if ($extension = $bundle->getContainerExtension()) {
                $container->registerExtension($extension);
            }
            if ($this->debug) {
                $container->addObjectResource($bundle);
            }
        }
        foreach ($this->bundles as $bundle) {
            $bundle->build($container);
        }
        $this->build($container);
        foreach ($container->getExtensions() as $extension) {
            $extensions[] = $extension->getAlias();
        }
        $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));
    }
    protected function getContainerBuilder()
    {
        $container = new ContainerBuilder();
        $container->getParameterBag()->add($this->getKernelParameters());
        if ($this instanceof CompilerPassInterface) {
            $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_OPTIMIZATION, -10000);
        }
        if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator')) {
            $container->setProxyInstantiator(new RuntimeInstantiator());
        }
        return $container;
    }
    protected function dumpContainer(ConfigCache $cache, ContainerBuilder $container, $class, $baseClass)
    {
        $dumper = new PhpDumper($container);
        if (class_exists('ProxyManager\Configuration') && class_exists('Symfony\Bridge\ProxyManager\LazyProxy\PhpDumper\ProxyDumper')) {
            $dumper->setProxyDumper(new ProxyDumper());
        }
        $content = $dumper->dump([
            'class' => $class,
            'base_class' => $baseClass,
            'file' => $cache->getPath(),
            'as_files' => true,
            'debug' => $this->debug,
            'build_time' => $container->hasParameter('kernel.container_build_time') ? $container->getParameter('kernel.container_build_time') : time(),
        ]);
        $rootCode = array_pop($content);
        $dir = \dirname($cache->getPath()).'/';
        $fs = new Filesystem();
        foreach ($content as $file => $code) {
            $fs->dumpFile($dir.$file, $code);
            @chmod($dir.$file, 0666 & ~umask());
        }
        $legacyFile = \dirname($dir.$file).'.legacy';
        if (file_exists($legacyFile)) {
            @unlink($legacyFile);
        }
        $cache->write($rootCode, $container->getResources());
    }
    protected function getContainerLoader(ContainerInterface $container)
    {
        $locator = new FileLocator($this);
        $resolver = new LoaderResolver([
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new GlobFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ]);
        return new DelegatingLoader($resolver);
    }
    public static function stripComments($source)
    {
        if (!\function_exists('token_get_all')) {
            return $source;
        }
        $rawChunk = '';
        $output = '';
        $tokens = token_get_all($source);
        $ignoreSpace = false;
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1]) || 'b"' === $token) {
                $rawChunk .= $token;
            } elseif (T_START_HEREDOC === $token[0]) {
                $output .= $rawChunk.$token[1];
                do {
                    $token = $tokens[++$i];
                    $output .= isset($token[1]) && 'b"' !== $token ? $token[1] : $token;
                } while (T_END_HEREDOC !== $token[0]);
                $rawChunk = '';
            } elseif (T_WHITESPACE === $token[0]) {
                if ($ignoreSpace) {
                    $ignoreSpace = false;
                    continue;
                }
                $rawChunk .= preg_replace(['/\n{2,}/S'], "\n", $token[1]);
            } elseif (\in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                $ignoreSpace = true;
            } else {
                $rawChunk .= $token[1];
                if (T_OPEN_TAG === $token[0]) {
                    $ignoreSpace = true;
                }
            }
        }
        $output .= $rawChunk;
        unset($tokens, $rawChunk);
        gc_mem_caches();
        return $output;
    }
    public function serialize()
    {
        return serialize([$this->environment, $this->debug]);
    }
    public function unserialize($data)
    {
        list($environment, $debug) = unserialize($data, ['allowed_classes' => false]);
        $this->__construct($environment, $debug);
    }
}
