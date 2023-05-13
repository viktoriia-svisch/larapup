<?php
namespace Psy;
use XdgBaseDir\Xdg;
class ConfigPaths
{
    public static function getConfigDirs()
    {
        $xdg = new Xdg();
        return self::getDirNames($xdg->getConfigDirs());
    }
    public static function getHomeConfigDirs()
    {
        $xdg = new Xdg();
        return self::getDirNames([$xdg->getHomeConfigDir()]);
    }
    public static function getCurrentConfigDir()
    {
        $configDirs = self::getHomeConfigDirs();
        foreach ($configDirs as $configDir) {
            if (@\is_dir($configDir)) {
                return $configDir;
            }
        }
        return $configDirs[0];
    }
    public static function getConfigFiles(array $names, $configDir = null)
    {
        $dirs = ($configDir === null) ? self::getConfigDirs() : [$configDir];
        return self::getRealFiles($dirs, $names);
    }
    public static function getDataDirs()
    {
        $xdg = new Xdg();
        return self::getDirNames($xdg->getDataDirs());
    }
    public static function getDataFiles(array $names, $dataDir = null)
    {
        $dirs = ($dataDir === null) ? self::getDataDirs() : [$dataDir];
        return self::getRealFiles($dirs, $names);
    }
    public static function getRuntimeDir()
    {
        $xdg = new Xdg();
        \set_error_handler(['Psy\Exception\ErrorException', 'throwException']);
        try {
            $runtimeDir = $xdg->getRuntimeDir(false);
        } catch (\Exception $e) {
            $runtimeDir = \sys_get_temp_dir();
        }
        \restore_error_handler();
        return \strtr($runtimeDir, '\\', '/') . '/psysh';
    }
    private static function getDirNames(array $baseDirs)
    {
        $dirs = \array_map(function ($dir) {
            return \strtr($dir, '\\', '/') . '/psysh';
        }, $baseDirs);
        if ($home = \getenv('HOME')) {
            $dirs[] = \strtr($home, '\\', '/') . '/.psysh';
        }
        if (\defined('PHP_WINDOWS_VERSION_MAJOR')) {
            if ($appData = \getenv('APPDATA')) {
                \array_unshift($dirs, \strtr($appData, '\\', '/') . '/PsySH');
            }
            $dir = \strtr(\getenv('HOMEDRIVE') . '/' . \getenv('HOMEPATH'), '\\', '/') . '/.psysh';
            if (!\in_array($dir, $dirs)) {
                $dirs[] = $dir;
            }
        }
        return $dirs;
    }
    private static function getRealFiles(array $dirNames, array $fileNames)
    {
        $files = [];
        foreach ($dirNames as $dir) {
            foreach ($fileNames as $name) {
                $file = $dir . '/' . $name;
                if (@\is_file($file)) {
                    $files[] = $file;
                }
            }
        }
        return $files;
    }
    public static function touchFileWithMkdir($file)
    {
        if (\file_exists($file)) {
            if (\is_writable($file)) {
                return $file;
            }
            \trigger_error(\sprintf('Writing to %s is not allowed.', $file), E_USER_NOTICE);
            return false;
        }
        $dir = \dirname($file);
        if (!\is_dir($dir)) {
            @\mkdir($dir, 0700, true);
        }
        if (!\is_dir($dir) || !\is_writable($dir)) {
            \trigger_error(\sprintf('Writing to %s is not allowed.', $dir), E_USER_NOTICE);
            return false;
        }
        \touch($file);
        return $file;
    }
}
