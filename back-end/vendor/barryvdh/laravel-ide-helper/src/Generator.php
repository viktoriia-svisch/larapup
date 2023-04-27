<?php
namespace Barryvdh\LaravelIdeHelper;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Collection;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;
class Generator
{
    protected $config;
    protected $view;
    protected $output;
    protected $extra = array();
    protected $magic = array();
    protected $interfaces = array();
    protected $helpers;
    public function __construct(
         $config,
         $view,
        OutputInterface $output = null,
        $helpers = ''
    ) {
        $this->config = $config;
        $this->view = $view;
        $this->detectDrivers();
        $this->extra = array_merge($this->extra, $this->config->get('ide-helper.extra'));
        $this->magic = array_merge($this->magic, $this->config->get('ide-helper.magic'));
        $this->interfaces = array_merge($this->interfaces, $this->config->get('ide-helper.interfaces'));
        foreach ($this->interfaces as &$interface) {
            $interface = '\\' . ltrim($interface, '\\');
        }
        $this->helpers = $helpers;
    }
    public function generate($format = 'php')
    {
        $method = 'generate'.ucfirst($format).'Helper';
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->generatePhpHelper();
    }
    public function generatePhpHelper()
    {
        $app = app();
        return $this->view->make('helper')
            ->with('namespaces_by_extends_ns', $this->getAliasesByExtendsNamespace())
            ->with('namespaces_by_alias_ns', $this->getAliasesByAliasNamespace())
            ->with('helpers', $this->helpers)
            ->with('version', $app->version())
            ->with('include_fluent', $this->config->get('ide-helper.include_fluent', true))
            ->render();
    }
    public function generateJsonHelper()
    {
        $classes = array();
        foreach ($this->getValidAliases() as $aliases) {
            foreach ($aliases as $alias) {
                $functions = array();
                foreach ($alias->getMethods() as $method) {
                    $functions[$method->getName()] = '('. $method->getParamsWithDefault().')';
                }
                $classes[$alias->getAlias()] = array(
                    'functions' => $functions,
                );
            }
        }
        $flags = JSON_FORCE_OBJECT;
        if (defined('JSON_PRETTY_PRINT')) {
            $flags |= JSON_PRETTY_PRINT;
        }
        return json_encode(array(
            'php' => array(
                'classes' => $classes,
            ),
        ), $flags);
    }
    protected function detectDrivers()
    {
        $defaultUserModel = config('auth.providers.users.model', config('auth.model', 'App\User'));
        $this->interfaces['\Illuminate\Contracts\Auth\Authenticatable'] = $defaultUserModel;
        try {
            if (class_exists('Auth') && is_a('Auth', '\Illuminate\Support\Facades\Auth', true)) {
                if (class_exists('\Illuminate\Foundation\Application')) {
                    $authMethod = version_compare(Application::VERSION, '5.2', '>=') ? 'guard' : 'driver';
                } else {
                    $refClass = new ReflectionClass('\Laravel\Lumen\Application');
                    $versionStr = $refClass->newInstanceWithoutConstructor()->version();
                    $authMethod = strpos($versionStr, 'Lumen (5.0') === 0 ?
                        'driver' :
                        (strpos($versionStr, 'Lumen (5.1') === 0 ? 'driver' : 'guard');
                }
                $class = get_class(\Auth::$authMethod());
                $this->extra['Auth'] = array($class);
                $this->interfaces['\Illuminate\Auth\UserProviderInterface'] = $class;
            }
        } catch (\Exception $e) {
        }
        try {
            if (class_exists('DB') && is_a('DB', '\Illuminate\Support\Facades\DB', true)) {
                $class = get_class(\DB::connection());
                $this->extra['DB'] = array($class);
                $this->interfaces['\Illuminate\Database\ConnectionInterface'] = $class;
            }
        } catch (\Exception $e) {
        }
        try {
            if (class_exists('Cache') && is_a('Cache', '\Illuminate\Support\Facades\Cache', true)) {
                $driver = get_class(\Cache::driver());
                $store = get_class(\Cache::getStore());
                $this->extra['Cache'] = array($driver, $store);
                $this->interfaces['\Illuminate\Cache\StoreInterface'] = $store;
            }
        } catch (\Exception $e) {
        }
        try {
            if (class_exists('Queue') && is_a('Queue', '\Illuminate\Support\Facades\Queue', true)) {
                $class = get_class(\Queue::connection());
                $this->extra['Queue'] = array($class);
                $this->interfaces['\Illuminate\Queue\QueueInterface'] = $class;
            }
        } catch (\Exception $e) {
        }
        try {
            if (class_exists('SSH') && is_a('SSH', '\Illuminate\Support\Facades\SSH', true)) {
                $class = get_class(\SSH::connection());
                $this->extra['SSH'] = array($class);
                $this->interfaces['\Illuminate\Remote\ConnectionInterface'] = $class;
            }
        } catch (\Exception $e) {
        }
        try {
            if (class_exists('Storage') && is_a('Storage', '\Illuminate\Support\Facades\Storage', true)) {
                $class = get_class(\Storage::disk());
                $this->extra['Storage'] = array($class);
                $this->interfaces['\Illuminate\Contracts\Filesystem\Filesystem'] = $class;
            }
        } catch (\Exception $e) {
        }
    }
    protected function getValidAliases()
    {
        $aliases = new Collection();
        foreach ($this->getAliases() as $name => $facade) {
            if ($facade == 'Illuminate\Support\Facades\Redis' && $name == 'Redis' && !class_exists('Predis\Client')) {
                continue;
            }
            $magicMethods = array_key_exists($name, $this->magic) ? $this->magic[$name] : array();
            $alias = new Alias($this->config, $name, $facade, $magicMethods, $this->interfaces);
            if ($alias->isValid()) {
                if (array_key_exists($name, $this->extra)) {
                    $alias->addClass($this->extra[$name]);
                }
                $aliases[] = $alias;
            }
        }
        return $aliases;
    }
    protected function getAliasesByExtendsNamespace()
    {
        return $this->getValidAliases()->groupBy(function (Alias $alias) {
            return $alias->getExtendsNamespace();
        });
    }
    protected function getAliasesByAliasNamespace()
    {
        return $this->getValidAliases()->groupBy(function (Alias $alias) {
            return $alias->getNamespace();
        });
    }
    protected function getAliases()
    {
        if (class_exists('Illuminate\Foundation\AliasLoader')) {
            return AliasLoader::getInstance()->getAliases();
        }
        $facades = [
          'App' => 'Illuminate\Support\Facades\App',
          'Auth' => 'Illuminate\Support\Facades\Auth',
          'Bus' => 'Illuminate\Support\Facades\Bus',
          'DB' => 'Illuminate\Support\Facades\DB',
          'Cache' => 'Illuminate\Support\Facades\Cache',
          'Cookie' => 'Illuminate\Support\Facades\Cookie',
          'Crypt' => 'Illuminate\Support\Facades\Crypt',
          'Event' => 'Illuminate\Support\Facades\Event',
          'Hash' => 'Illuminate\Support\Facades\Hash',
          'Log' => 'Illuminate\Support\Facades\Log',
          'Mail' => 'Illuminate\Support\Facades\Mail',
          'Queue' => 'Illuminate\Support\Facades\Queue',
          'Request' => 'Illuminate\Support\Facades\Request',
          'Schema' => 'Illuminate\Support\Facades\Schema',
          'Session' => 'Illuminate\Support\Facades\Session',
          'Storage' => 'Illuminate\Support\Facades\Storage',
          'Validator' => 'Illuminate\Support\Facades\Validator',
          'Gate' => 'Illuminate\Support\Facades\Gate',
        ];
        $facades = array_merge($facades, $this->config->get('app.aliases', []));
        return array_filter(
            $facades,
            function ($alias) {
                return class_exists($alias);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
    public function getDriver($alias)
    {
        try {
            if ($alias == "Auth") {
                $driver = \Auth::driver();
            } elseif ($alias == "DB") {
                $driver = \DB::connection();
            } elseif ($alias == "Cache") {
                $driver = get_class(\Cache::driver());
                $store = get_class(\Cache::getStore());
                return array($driver, $store);
            } elseif ($alias == "Queue") {
                $driver = \Queue::connection();
            } else {
                return false;
            }
            return get_class($driver);
        } catch (\Exception $e) {
            $this->error("Could not determine driver/connection for $alias.");
            return false;
        }
    }
    protected function error($string)
    {
        if ($this->output) {
            $this->output->writeln("<error>$string</error>");
        } else {
            echo $string . "\r\n";
        }
    }
}
