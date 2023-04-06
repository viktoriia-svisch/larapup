<?php
namespace Psy;
use Psy\Exception\DeprecatedException;
use Psy\Exception\RuntimeException;
use Psy\Output\OutputPager;
use Psy\Output\ShellOutput;
use Psy\Readline\GNUReadline;
use Psy\Readline\HoaConsole;
use Psy\Readline\Libedit;
use Psy\Readline\Readline;
use Psy\Readline\Transient;
use Psy\TabCompletion\AutoCompleter;
use Psy\VarDumper\Presenter;
use Psy\VersionUpdater\Checker;
use Psy\VersionUpdater\GitHubChecker;
use Psy\VersionUpdater\IntervalChecker;
use Psy\VersionUpdater\NoopChecker;
class Configuration
{
    const COLOR_MODE_AUTO     = 'auto';
    const COLOR_MODE_FORCED   = 'forced';
    const COLOR_MODE_DISABLED = 'disabled';
    private static $AVAILABLE_OPTIONS = [
        'codeCleaner',
        'colorMode',
        'configDir',
        'dataDir',
        'defaultIncludes',
        'eraseDuplicates',
        'errorLoggingLevel',
        'forceArrayIndexes',
        'historySize',
        'manualDbFile',
        'pager',
        'prompt',
        'requireSemicolons',
        'runtimeDir',
        'startupMessage',
        'updateCheck',
        'useBracketedPaste',
        'usePcntl',
        'useReadline',
        'useTabCompletion',
        'useUnicode',
        'warnOnMultipleConfigs',
    ];
    private $defaultIncludes;
    private $configDir;
    private $dataDir;
    private $runtimeDir;
    private $configFile;
    private $historyFile;
    private $historySize;
    private $eraseDuplicates;
    private $manualDbFile;
    private $hasReadline;
    private $useReadline;
    private $useBracketedPaste;
    private $hasPcntl;
    private $usePcntl;
    private $newCommands       = [];
    private $requireSemicolons = false;
    private $useUnicode;
    private $useTabCompletion;
    private $newMatchers = [];
    private $errorLoggingLevel = E_ALL;
    private $warnOnMultipleConfigs = false;
    private $colorMode;
    private $updateCheck;
    private $startupMessage;
    private $forceArrayIndexes = false;
    private $readline;
    private $output;
    private $shell;
    private $cleaner;
    private $pager;
    private $manualDb;
    private $presenter;
    private $autoCompleter;
    private $checker;
    private $prompt;
    public function __construct(array $config = [])
    {
        $this->setColorMode(self::COLOR_MODE_AUTO);
        if (isset($config['configFile'])) {
            $this->configFile = $config['configFile'];
        } elseif ($configFile = \getenv('PSYSH_CONFIG')) {
            $this->configFile = $configFile;
        }
        if (isset($config['baseDir'])) {
            $msg = "The 'baseDir' configuration option is deprecated; " .
                "please specify 'configDir' and 'dataDir' options instead";
            throw new DeprecatedException($msg);
        }
        unset($config['configFile'], $config['baseDir']);
        $this->loadConfig($config);
        $this->init();
    }
    public function init()
    {
        $this->hasReadline = \function_exists('readline');
        $this->hasPcntl    = \function_exists('pcntl_signal') && \function_exists('posix_getpid');
        if ($configFile = $this->getConfigFile()) {
            $this->loadConfigFile($configFile);
        }
        if (!$this->configFile && $localConfig = $this->getLocalConfigFile()) {
            $this->loadConfigFile($localConfig);
        }
    }
    public function getConfigFile()
    {
        if (isset($this->configFile)) {
            return $this->configFile;
        }
        $files = ConfigPaths::getConfigFiles(['config.php', 'rc.php'], $this->configDir);
        if (!empty($files)) {
            if ($this->warnOnMultipleConfigs && \count($files) > 1) {
                $msg = \sprintf('Multiple configuration files found: %s. Using %s', \implode($files, ', '), $files[0]);
                \trigger_error($msg, E_USER_NOTICE);
            }
            return $files[0];
        }
    }
    public function getLocalConfigFile()
    {
        $localConfig = \getcwd() . '/.psysh.php';
        if (@\is_file($localConfig)) {
            return $localConfig;
        }
    }
    public function loadConfig(array $options)
    {
        foreach (self::$AVAILABLE_OPTIONS as $option) {
            if (isset($options[$option])) {
                $method = 'set' . \ucfirst($option);
                $this->$method($options[$option]);
            }
        }
        if (isset($options['tabCompletion'])) {
            $msg = '`tabCompletion` is deprecated; use `useTabCompletion` instead.';
            @\trigger_error($msg, E_USER_DEPRECATED);
            $this->setUseTabCompletion($options['tabCompletion']);
        }
        foreach (['commands', 'matchers', 'casters'] as $option) {
            if (isset($options[$option])) {
                $method = 'add' . \ucfirst($option);
                $this->$method($options[$option]);
            }
        }
        if (isset($options['tabCompletionMatchers'])) {
            $msg = '`tabCompletionMatchers` is deprecated; use `matchers` instead.';
            @\trigger_error($msg, E_USER_DEPRECATED);
            $this->addMatchers($options['tabCompletionMatchers']);
        }
    }
    public function loadConfigFile($file)
    {
        $__psysh_config_file__ = $file;
        $load = function ($config) use ($__psysh_config_file__) {
            $result = require $__psysh_config_file__;
            if ($result !== 1) {
                return $result;
            }
        };
        $result = $load($this);
        if (!empty($result)) {
            if (\is_array($result)) {
                $this->loadConfig($result);
            } else {
                throw new \InvalidArgumentException('Psy Shell configuration must return an array of options');
            }
        }
    }
    public function setDefaultIncludes(array $includes = [])
    {
        $this->defaultIncludes = $includes;
    }
    public function getDefaultIncludes()
    {
        return $this->defaultIncludes ?: [];
    }
    public function setConfigDir($dir)
    {
        $this->configDir = (string) $dir;
    }
    public function getConfigDir()
    {
        return $this->configDir;
    }
    public function setDataDir($dir)
    {
        $this->dataDir = (string) $dir;
    }
    public function getDataDir()
    {
        return $this->dataDir;
    }
    public function setRuntimeDir($dir)
    {
        $this->runtimeDir = (string) $dir;
    }
    public function getRuntimeDir()
    {
        if (!isset($this->runtimeDir)) {
            $this->runtimeDir = ConfigPaths::getRuntimeDir();
        }
        if (!\is_dir($this->runtimeDir)) {
            \mkdir($this->runtimeDir, 0700, true);
        }
        return $this->runtimeDir;
    }
    public function setHistoryFile($file)
    {
        $this->historyFile = ConfigPaths::touchFileWithMkdir($file);
    }
    public function getHistoryFile()
    {
        if (isset($this->historyFile)) {
            return $this->historyFile;
        }
        $files = ConfigPaths::getConfigFiles(['psysh_history', 'history'], $this->configDir);
        if (!empty($files)) {
            if ($this->warnOnMultipleConfigs && \count($files) > 1) {
                $msg = \sprintf('Multiple history files found: %s. Using %s', \implode($files, ', '), $files[0]);
                \trigger_error($msg, E_USER_NOTICE);
            }
            $this->setHistoryFile($files[0]);
        } else {
            $dir = $this->configDir ?: ConfigPaths::getCurrentConfigDir();
            $this->setHistoryFile($dir . '/psysh_history');
        }
        return $this->historyFile;
    }
    public function setHistorySize($value)
    {
        $this->historySize = (int) $value;
    }
    public function getHistorySize()
    {
        return $this->historySize;
    }
    public function setEraseDuplicates($value)
    {
        $this->eraseDuplicates = (bool) $value;
    }
    public function getEraseDuplicates()
    {
        return $this->eraseDuplicates;
    }
    public function getTempFile($type, $pid)
    {
        return \tempnam($this->getRuntimeDir(), $type . '_' . $pid . '_');
    }
    public function getPipe($type, $pid)
    {
        return \sprintf('%s/%s_%s', $this->getRuntimeDir(), $type, $pid);
    }
    public function hasReadline()
    {
        return $this->hasReadline;
    }
    public function setUseReadline($useReadline)
    {
        $this->useReadline = (bool) $useReadline;
    }
    public function useReadline()
    {
        return isset($this->useReadline) ? ($this->hasReadline && $this->useReadline) : $this->hasReadline;
    }
    public function setReadline(Readline $readline)
    {
        $this->readline = $readline;
    }
    public function getReadline()
    {
        if (!isset($this->readline)) {
            $className = $this->getReadlineClass();
            $this->readline = new $className(
                $this->getHistoryFile(),
                $this->getHistorySize(),
                $this->getEraseDuplicates()
            );
        }
        return $this->readline;
    }
    private function getReadlineClass()
    {
        if ($this->useReadline()) {
            if (GNUReadline::isSupported()) {
                return 'Psy\Readline\GNUReadline';
            } elseif (Libedit::isSupported()) {
                return 'Psy\Readline\Libedit';
            } elseif (HoaConsole::isSupported()) {
                return 'Psy\Readline\HoaConsole';
            }
        }
        return 'Psy\Readline\Transient';
    }
    public function setUseBracketedPaste($useBracketedPaste)
    {
        $this->useBracketedPaste = (bool) $useBracketedPaste;
    }
    public function useBracketedPaste()
    {
        $supported = ($this->getReadlineClass() === 'Psy\Readline\GNUReadline');
        return $supported && $this->useBracketedPaste;
    }
    public function hasPcntl()
    {
        return $this->hasPcntl;
    }
    public function setUsePcntl($usePcntl)
    {
        $this->usePcntl = (bool) $usePcntl;
    }
    public function usePcntl()
    {
        return isset($this->usePcntl) ? ($this->hasPcntl && $this->usePcntl) : $this->hasPcntl;
    }
    public function setRequireSemicolons($requireSemicolons)
    {
        $this->requireSemicolons = (bool) $requireSemicolons;
    }
    public function requireSemicolons()
    {
        return $this->requireSemicolons;
    }
    public function setUseUnicode($useUnicode)
    {
        $this->useUnicode = (bool) $useUnicode;
    }
    public function useUnicode()
    {
        if (isset($this->useUnicode)) {
            return $this->useUnicode;
        }
        return true;
    }
    public function setErrorLoggingLevel($errorLoggingLevel)
    {
        $this->errorLoggingLevel = (E_ALL | E_STRICT) & $errorLoggingLevel;
    }
    public function errorLoggingLevel()
    {
        return $this->errorLoggingLevel;
    }
    public function setCodeCleaner(CodeCleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }
    public function getCodeCleaner()
    {
        if (!isset($this->cleaner)) {
            $this->cleaner = new CodeCleaner();
        }
        return $this->cleaner;
    }
    public function setUseTabCompletion($useTabCompletion)
    {
        $this->useTabCompletion = (bool) $useTabCompletion;
    }
    public function setTabCompletion($useTabCompletion)
    {
        $this->setUseTabCompletion($useTabCompletion);
    }
    public function useTabCompletion()
    {
        return isset($this->useTabCompletion) ? ($this->hasReadline && $this->useTabCompletion) : $this->hasReadline;
    }
    public function getTabCompletion()
    {
        return $this->useTabCompletion();
    }
    public function setOutput(ShellOutput $output)
    {
        $this->output = $output;
    }
    public function getOutput()
    {
        if (!isset($this->output)) {
            $this->output = new ShellOutput(
                ShellOutput::VERBOSITY_NORMAL,
                $this->getOutputDecorated(),
                null,
                $this->getPager()
            );
        }
        return $this->output;
    }
    public function getOutputDecorated()
    {
        if ($this->colorMode() === self::COLOR_MODE_AUTO) {
            return;
        } elseif ($this->colorMode() === self::COLOR_MODE_FORCED) {
            return true;
        } elseif ($this->colorMode() === self::COLOR_MODE_DISABLED) {
            return false;
        }
    }
    public function setPager($pager)
    {
        if ($pager && !\is_string($pager) && !$pager instanceof OutputPager) {
            throw new \InvalidArgumentException('Unexpected pager instance');
        }
        $this->pager = $pager;
    }
    public function getPager()
    {
        if (!isset($this->pager) && $this->usePcntl()) {
            if ($pager = \ini_get('cli.pager')) {
                $this->pager = $pager;
            } elseif ($less = \exec('which less 2>/dev/null')) {
                $this->pager = $less . ' -R -S -F -X';
            }
        }
        return $this->pager;
    }
    public function setAutoCompleter(AutoCompleter $autoCompleter)
    {
        $this->autoCompleter = $autoCompleter;
    }
    public function getAutoCompleter()
    {
        if (!isset($this->autoCompleter)) {
            $this->autoCompleter = new AutoCompleter();
        }
        return $this->autoCompleter;
    }
    public function getTabCompletionMatchers()
    {
        return [];
    }
    public function addMatchers(array $matchers)
    {
        $this->newMatchers = \array_merge($this->newMatchers, $matchers);
        if (isset($this->shell)) {
            $this->doAddMatchers();
        }
    }
    private function doAddMatchers()
    {
        if (!empty($this->newMatchers)) {
            $this->shell->addMatchers($this->newMatchers);
            $this->newMatchers = [];
        }
    }
    public function addTabCompletionMatchers(array $matchers)
    {
        $this->addMatchers($matchers);
    }
    public function addCommands(array $commands)
    {
        $this->newCommands = \array_merge($this->newCommands, $commands);
        if (isset($this->shell)) {
            $this->doAddCommands();
        }
    }
    private function doAddCommands()
    {
        if (!empty($this->newCommands)) {
            $this->shell->addCommands($this->newCommands);
            $this->newCommands = [];
        }
    }
    public function setShell(Shell $shell)
    {
        $this->shell = $shell;
        $this->doAddCommands();
        $this->doAddMatchers();
    }
    public function setManualDbFile($filename)
    {
        $this->manualDbFile = (string) $filename;
    }
    public function getManualDbFile()
    {
        if (isset($this->manualDbFile)) {
            return $this->manualDbFile;
        }
        $files = ConfigPaths::getDataFiles(['php_manual.sqlite'], $this->dataDir);
        if (!empty($files)) {
            if ($this->warnOnMultipleConfigs && \count($files) > 1) {
                $msg = \sprintf('Multiple manual database files found: %s. Using %s', \implode($files, ', '), $files[0]);
                \trigger_error($msg, E_USER_NOTICE);
            }
            return $this->manualDbFile = $files[0];
        }
    }
    public function getManualDb()
    {
        if (!isset($this->manualDb)) {
            $dbFile = $this->getManualDbFile();
            if (\is_file($dbFile)) {
                try {
                    $this->manualDb = new \PDO('sqlite:' . $dbFile);
                } catch (\PDOException $e) {
                    if ($e->getMessage() === 'could not find driver') {
                        throw new RuntimeException('SQLite PDO driver not found', 0, $e);
                    } else {
                        throw $e;
                    }
                }
            }
        }
        return $this->manualDb;
    }
    public function addCasters(array $casters)
    {
        $this->getPresenter()->addCasters($casters);
    }
    public function getPresenter()
    {
        if (!isset($this->presenter)) {
            $this->presenter = new Presenter($this->getOutput()->getFormatter(), $this->forceArrayIndexes());
        }
        return $this->presenter;
    }
    public function setWarnOnMultipleConfigs($warnOnMultipleConfigs)
    {
        $this->warnOnMultipleConfigs = (bool) $warnOnMultipleConfigs;
    }
    public function warnOnMultipleConfigs()
    {
        return $this->warnOnMultipleConfigs;
    }
    public function setColorMode($colorMode)
    {
        $validColorModes = [
            self::COLOR_MODE_AUTO,
            self::COLOR_MODE_FORCED,
            self::COLOR_MODE_DISABLED,
        ];
        if (\in_array($colorMode, $validColorModes)) {
            $this->colorMode = $colorMode;
        } else {
            throw new \InvalidArgumentException('invalid color mode: ' . $colorMode);
        }
    }
    public function colorMode()
    {
        return $this->colorMode;
    }
    public function setChecker(Checker $checker)
    {
        $this->checker = $checker;
    }
    public function getChecker()
    {
        if (!isset($this->checker)) {
            $interval = $this->getUpdateCheck();
            switch ($interval) {
                case Checker::ALWAYS:
                    $this->checker = new GitHubChecker();
                    break;
                case Checker::DAILY:
                case Checker::WEEKLY:
                case Checker::MONTHLY:
                    $checkFile = $this->getUpdateCheckCacheFile();
                    if ($checkFile === false) {
                        $this->checker = new NoopChecker();
                    } else {
                        $this->checker = new IntervalChecker($checkFile, $interval);
                    }
                    break;
                case Checker::NEVER:
                    $this->checker = new NoopChecker();
                    break;
            }
        }
        return $this->checker;
    }
    public function getUpdateCheck()
    {
        return isset($this->updateCheck) ? $this->updateCheck : Checker::WEEKLY;
    }
    public function setUpdateCheck($interval)
    {
        $validIntervals = [
            Checker::ALWAYS,
            Checker::DAILY,
            Checker::WEEKLY,
            Checker::MONTHLY,
            Checker::NEVER,
        ];
        if (!\in_array($interval, $validIntervals)) {
            throw new \InvalidArgumentException('invalid update check interval: ' . $interval);
        }
        $this->updateCheck = $interval;
    }
    public function getUpdateCheckCacheFile()
    {
        $dir = $this->configDir ?: ConfigPaths::getCurrentConfigDir();
        return ConfigPaths::touchFileWithMkdir($dir . '/update_check.json');
    }
    public function setStartupMessage($message)
    {
        $this->startupMessage = $message;
    }
    public function getStartupMessage()
    {
        return $this->startupMessage;
    }
    public function setPrompt($prompt)
    {
        $this->prompt = $prompt;
    }
    public function getPrompt()
    {
        return $this->prompt;
    }
    public function forceArrayIndexes()
    {
        return $this->forceArrayIndexes;
    }
    public function setForceArrayIndexes($forceArrayIndexes)
    {
        $this->forceArrayIndexes = $forceArrayIndexes;
    }
}
