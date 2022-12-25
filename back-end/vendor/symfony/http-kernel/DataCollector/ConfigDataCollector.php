<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\VarDumper\Caster\LinkStub;
class ConfigDataCollector extends DataCollector implements LateDataCollectorInterface
{
    private $kernel;
    private $name;
    private $version;
    private $hasVarDumper;
    public function __construct(string $name = null, string $version = null)
    {
        if (1 <= \func_num_args()) {
            @trigger_error(sprintf('The "$name" argument in method "%s()" is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        }
        if (2 <= \func_num_args()) {
            @trigger_error(sprintf('The "$version" argument in method "%s()" is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        }
        $this->name = $name;
        $this->version = $version;
        $this->hasVarDumper = class_exists(LinkStub::class);
    }
    public function setKernel(KernelInterface $kernel = null)
    {
        $this->kernel = $kernel;
    }
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'app_name' => $this->name,
            'app_version' => $this->version,
            'token' => $response->headers->get('X-Debug-Token'),
            'symfony_version' => Kernel::VERSION,
            'symfony_state' => 'unknown',
            'env' => isset($this->kernel) ? $this->kernel->getEnvironment() : 'n/a',
            'debug' => isset($this->kernel) ? $this->kernel->isDebug() : 'n/a',
            'php_version' => PHP_VERSION,
            'php_architecture' => PHP_INT_SIZE * 8,
            'php_intl_locale' => class_exists('Locale', false) && \Locale::getDefault() ? \Locale::getDefault() : 'n/a',
            'php_timezone' => date_default_timezone_get(),
            'xdebug_enabled' => \extension_loaded('xdebug'),
            'apcu_enabled' => \extension_loaded('apcu') && filter_var(ini_get('apc.enabled'), FILTER_VALIDATE_BOOLEAN),
            'zend_opcache_enabled' => \extension_loaded('Zend OPcache') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN),
            'bundles' => [],
            'sapi_name' => \PHP_SAPI,
        ];
        if (isset($this->kernel)) {
            foreach ($this->kernel->getBundles() as $name => $bundle) {
                $this->data['bundles'][$name] = $this->hasVarDumper ? new LinkStub($bundle->getPath()) : $bundle->getPath();
            }
            $this->data['symfony_state'] = $this->determineSymfonyState();
            $this->data['symfony_minor_version'] = sprintf('%s.%s', Kernel::MAJOR_VERSION, Kernel::MINOR_VERSION);
            $eom = \DateTime::createFromFormat('m/Y', Kernel::END_OF_MAINTENANCE);
            $eol = \DateTime::createFromFormat('m/Y', Kernel::END_OF_LIFE);
            $this->data['symfony_eom'] = $eom->format('F Y');
            $this->data['symfony_eol'] = $eol->format('F Y');
        }
        if (preg_match('~^(\d+(?:\.\d+)*)(.+)?$~', $this->data['php_version'], $matches) && isset($matches[2])) {
            $this->data['php_version'] = $matches[1];
            $this->data['php_version_extra'] = $matches[2];
        }
    }
    public function reset()
    {
        $this->data = [];
    }
    public function lateCollect()
    {
        $this->data = $this->cloneVar($this->data);
    }
    public function getApplicationName()
    {
        @trigger_error(sprintf('The method "%s()" is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        return $this->data['app_name'];
    }
    public function getApplicationVersion()
    {
        @trigger_error(sprintf('The method "%s()" is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        return $this->data['app_version'];
    }
    public function getToken()
    {
        return $this->data['token'];
    }
    public function getSymfonyVersion()
    {
        return $this->data['symfony_version'];
    }
    public function getSymfonyState()
    {
        return $this->data['symfony_state'];
    }
    public function getSymfonyMinorVersion()
    {
        return $this->data['symfony_minor_version'];
    }
    public function getSymfonyEom()
    {
        return $this->data['symfony_eom'];
    }
    public function getSymfonyEol()
    {
        return $this->data['symfony_eol'];
    }
    public function getPhpVersion()
    {
        return $this->data['php_version'];
    }
    public function getPhpVersionExtra()
    {
        return isset($this->data['php_version_extra']) ? $this->data['php_version_extra'] : null;
    }
    public function getPhpArchitecture()
    {
        return $this->data['php_architecture'];
    }
    public function getPhpIntlLocale()
    {
        return $this->data['php_intl_locale'];
    }
    public function getPhpTimezone()
    {
        return $this->data['php_timezone'];
    }
    public function getAppName()
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2.', __METHOD__), E_USER_DEPRECATED);
        return 'n/a';
    }
    public function getEnv()
    {
        return $this->data['env'];
    }
    public function isDebug()
    {
        return $this->data['debug'];
    }
    public function hasXDebug()
    {
        return $this->data['xdebug_enabled'];
    }
    public function hasApcu()
    {
        return $this->data['apcu_enabled'];
    }
    public function hasZendOpcache()
    {
        return $this->data['zend_opcache_enabled'];
    }
    public function getBundles()
    {
        return $this->data['bundles'];
    }
    public function getSapiName()
    {
        return $this->data['sapi_name'];
    }
    public function getName()
    {
        return 'config';
    }
    private function determineSymfonyState()
    {
        $now = new \DateTime();
        $eom = \DateTime::createFromFormat('m/Y', Kernel::END_OF_MAINTENANCE)->modify('last day of this month');
        $eol = \DateTime::createFromFormat('m/Y', Kernel::END_OF_LIFE)->modify('last day of this month');
        if ($now > $eol) {
            $versionState = 'eol';
        } elseif ($now > $eom) {
            $versionState = 'eom';
        } elseif ('' !== Kernel::EXTRA_VERSION) {
            $versionState = 'dev';
        } else {
            $versionState = 'stable';
        }
        return $versionState;
    }
}
