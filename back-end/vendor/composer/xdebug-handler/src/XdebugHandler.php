<?php
namespace Composer\XdebugHandler;
use Psr\Log\LoggerInterface;
class XdebugHandler
{
    const SUFFIX_ALLOW = '_ALLOW_XDEBUG';
    const SUFFIX_INIS = '_ORIGINAL_INIS';
    const RESTART_ID = 'internal';
    const RESTART_SETTINGS = 'XDEBUG_HANDLER_SETTINGS';
    const DEBUG = 'XDEBUG_HANDLER_DEBUG';
    protected $tmpIni;
    private static $inRestart;
    private static $name;
    private static $skipped;
    private $cli;
    private $colorOption;
    private $debug;
    private $envAllowXdebug;
    private $envOriginalInis;
    private $loaded;
    private $persistent;
    private $script;
    private $statusWriter;
    public function __construct($envPrefix, $colorOption = '')
    {
        if (!is_string($envPrefix) || empty($envPrefix) || !is_string($colorOption)) {
            throw new \RuntimeException('Invalid constructor parameter');
        }
        self::$name = strtoupper($envPrefix);
        $this->envAllowXdebug = self::$name.self::SUFFIX_ALLOW;
        $this->envOriginalInis = self::$name.self::SUFFIX_INIS;
        $this->colorOption = $colorOption;
        if (extension_loaded('xdebug')) {
            $ext = new \ReflectionExtension('xdebug');
            $this->loaded = $ext->getVersion() ?: 'unknown';
        }
        if ($this->cli = PHP_SAPI === 'cli') {
            $this->debug = getenv(self::DEBUG);
        }
        $this->statusWriter = new Status($this->envAllowXdebug, (bool) $this->debug);
    }
    public function setLogger(LoggerInterface $logger)
    {
        $this->statusWriter->setLogger($logger);
        return $this;
    }
    public function setMainScript($script)
    {
        $this->script = $script;
        return $this;
    }
    public function setPersistent()
    {
        $this->persistent = true;
        return $this;
    }
    public function check()
    {
        $this->notify(Status::CHECK, $this->loaded);
        $envArgs = explode('|', (string) getenv($this->envAllowXdebug));
        if (empty($envArgs[0]) && $this->requiresRestart((bool) $this->loaded)) {
            $this->notify(Status::RESTART);
            if ($this->prepareRestart()) {
                $command = $this->getCommand();
                $this->notify(Status::RESTARTING, $command);
                $this->restart($command);
            }
            return;
        }
        if (self::RESTART_ID === $envArgs[0] && count($envArgs) === 5) {
            $this->notify(Status::RESTARTED);
            Process::setEnv($this->envAllowXdebug);
            self::$inRestart = true;
            if (!$this->loaded) {
                self::$skipped = $envArgs[1];
            }
            $this->setEnvRestartSettings($envArgs);
            return;
        }
        $this->notify(Status::NORESTART);
        if ($settings = self::getRestartSettings()) {
            $this->syncSettings($settings);
        }
    }
    public static function getAllIniFiles()
    {
        if (!empty(self::$name)) {
            $env = getenv(self::$name.self::SUFFIX_INIS);
            if (false !== $env) {
                return explode(PATH_SEPARATOR, $env);
            }
        }
        $paths = array((string) php_ini_loaded_file());
        if ($scanned = php_ini_scanned_files()) {
            $paths = array_merge($paths, array_map('trim', explode(',', $scanned)));
        }
        return $paths;
    }
    public static function getRestartSettings()
    {
        $envArgs = explode('|', (string) getenv(self::RESTART_SETTINGS));
        if (count($envArgs) !== 6
            || (!self::$inRestart && php_ini_loaded_file() !== $envArgs[0])) {
            return;
        }
        return array(
            'tmpIni' => $envArgs[0],
            'scannedInis' => (bool) $envArgs[1],
            'scanDir' => '*' === $envArgs[2] ? false : $envArgs[2],
            'phprc' => '*' === $envArgs[3] ? false : $envArgs[3],
            'inis' => explode(PATH_SEPARATOR, $envArgs[4]),
            'skipped' => $envArgs[5],
        );
    }
    public static function getSkippedVersion()
    {
        return (string) self::$skipped;
    }
    protected function requiresRestart($isLoaded)
    {
        return $isLoaded;
    }
    protected function restart($command)
    {
        $this->doRestart($command);
    }
    private function doRestart($command)
    {
        passthru($command, $exitCode);
        $this->notify(Status::INFO, 'Restarted process exited '.$exitCode);
        if ($this->debug === '2') {
            $this->notify(Status::INFO, 'Temp ini saved: '.$this->tmpIni);
        } else {
            @unlink($this->tmpIni);
        }
        exit($exitCode);
    }
    private function prepareRestart()
    {
        $error = '';
        $iniFiles = self::getAllIniFiles();
        $scannedInis = count($iniFiles) > 1;
        $tmpDir = sys_get_temp_dir();
        if (!$this->cli) {
            $error = 'Unsupported SAPI: '.PHP_SAPI;
        } elseif (!defined('PHP_BINARY')) {
            $error = 'PHP version is too old: '.PHP_VERSION;
        } elseif (!$this->checkConfiguration($info)) {
            $error = $info;
        } elseif (!$this->checkScanDirConfig()) {
            $error = 'PHP version does not report scanned inis: '.PHP_VERSION;
        } elseif (!$this->checkMainScript()) {
            $error = 'Unable to access main script: '.$this->script;
        } elseif (!$this->writeTmpIni($iniFiles, $tmpDir, $error)) {
            $error = $error ?: 'Unable to create temp ini file at: '.$tmpDir;
        } elseif (!$this->setEnvironment($scannedInis, $iniFiles)) {
            $error = 'Unable to set environment variables';
        }
        if ($error) {
            $this->notify(Status::ERROR, $error);
        }
        return empty($error);
    }
    private function writeTmpIni(array $iniFiles, $tmpDir, &$error)
    {
        if (!$this->tmpIni = @tempnam($tmpDir, '')) {
            return false;
        }
        if (empty($iniFiles[0])) {
            array_shift($iniFiles);
        }
        $content = '';
        $regex = '/^\s*(zend_extension\s*=.*xdebug.*)$/mi';
        foreach ($iniFiles as $file) {
            if (!$data = @file_get_contents($file)) {
                $error = 'Unable to read ini: '.$file;
                return false;
            }
            $content .= preg_replace($regex, ';$1', $data).PHP_EOL;
        }
        if ($config = parse_ini_string($content)) {
            $loaded = ini_get_all(null, false);
            $content .= $this->mergeLoadedConfig($loaded, $config);
        }
        $content .= 'opcache.enable_cli=0'.PHP_EOL;
        return @file_put_contents($this->tmpIni, $content);
    }
    private function getCommand()
    {
        $php = array(PHP_BINARY);
        $args = array_slice($_SERVER['argv'], 1);
        if (!$this->persistent) {
            array_push($php, '-n', '-c', $this->tmpIni);
        }
        if (defined('STDOUT') && Process::supportsColor(STDOUT)) {
            $args = Process::addColorOption($args, $this->colorOption);
        }
        $args = array_merge($php, array($this->script), $args);
        $cmd = Process::escape(array_shift($args), true, true);
        foreach ($args as $arg) {
            $cmd .= ' '.Process::escape($arg);
        }
        return $cmd;
    }
    private function setEnvironment($scannedInis, array $iniFiles)
    {
        $scanDir = getenv('PHP_INI_SCAN_DIR');
        $phprc = getenv('PHPRC');
        if (!putenv($this->envOriginalInis.'='.implode(PATH_SEPARATOR, $iniFiles))) {
            return false;
        }
        if ($this->persistent) {
            if (!putenv('PHP_INI_SCAN_DIR=') || !putenv('PHPRC='.$this->tmpIni)) {
                return false;
            }
        }
        $envArgs = array(
            self::RESTART_ID,
            $this->loaded,
            (int) $scannedInis,
            false === $scanDir ? '*' : $scanDir,
            false === $phprc ? '*' : $phprc,
        );
        return putenv($this->envAllowXdebug.'='.implode('|', $envArgs));
    }
    private function notify($op, $data = null)
    {
        $this->statusWriter->report($op, $data);
    }
    private function mergeLoadedConfig(array $loadedConfig, array $iniConfig)
    {
        $content = '';
        foreach ($loadedConfig as $name => $value) {
            if (!is_string($value)
                || strpos($name, 'xdebug') === 0
                || $name === 'apc.mmap_file_mask') {
                continue;
            }
            if (!isset($iniConfig[$name]) || $iniConfig[$name] !== $value) {
                $content .= $name.'="'.addcslashes($value, '\\"').'"'.PHP_EOL;
            }
        }
        return $content;
    }
    private function checkMainScript()
    {
        if (null !== $this->script) {
            return file_exists($this->script) || '--' === $this->script;
        }
        if (file_exists($this->script = $_SERVER['argv'][0])) {
            return true;
        }
        $options = PHP_VERSION_ID >= 50306 ? DEBUG_BACKTRACE_IGNORE_ARGS : false;
        $trace = debug_backtrace($options);
        if (($main = end($trace)) && isset($main['file'])) {
            return file_exists($this->script = $main['file']);
        }
        return false;
    }
    private function setEnvRestartSettings($envArgs)
    {
        $settings = array(
            php_ini_loaded_file(),
            $envArgs[2],
            $envArgs[3],
            $envArgs[4],
            getenv($this->envOriginalInis),
            self::$skipped,
        );
        Process::setEnv(self::RESTART_SETTINGS, implode('|', $settings));
    }
    private function syncSettings(array $settings)
    {
        if (false === getenv($this->envOriginalInis)) {
            Process::setEnv($this->envOriginalInis, implode(PATH_SEPARATOR, $settings['inis']));
        }
        self::$skipped = $settings['skipped'];
        $this->notify(Status::INFO, 'Process called with existing restart settings');
    }
    private function checkScanDirConfig()
    {
        return !(getenv('PHP_INI_SCAN_DIR')
            && !PHP_CONFIG_FILE_SCAN_DIR
            && (PHP_VERSION_ID < 70113
            || PHP_VERSION_ID === 70200));
    }
    private function checkConfiguration(&$info)
    {
        if (false !== strpos(ini_get('disable_functions'), 'passthru')) {
            $info = 'passthru function is disabled';
            return false;
        }
        if (extension_loaded('uopz')) {
            if (function_exists('uopz_allow_exit')) {
                @uopz_allow_exit(true);
            } else {
                $info = 'uopz extension is not compatible';
                return false;
            }
        }
        return true;
    }
}
