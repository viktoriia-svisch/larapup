<?php
namespace XdgBaseDir;
class Xdg
{
    const S_IFDIR = 040000; 
    const S_IRWXO = 00007;  
    const S_IRWXG = 00056;  
    const RUNTIME_DIR_FALLBACK = 'php-xdg-runtime-dir-fallback-';
    public function getHomeDir()
    {
        return getenv('HOME') ?: (getenv('HOMEDRIVE') . DIRECTORY_SEPARATOR . getenv('HOMEPATH'));
    }
    public function getHomeConfigDir()
    {
        $path = getenv('XDG_CONFIG_HOME') ?: $this->getHomeDir() . DIRECTORY_SEPARATOR . '.config';
        return $path;
    }
    public function getHomeDataDir()
    {
        $path = getenv('XDG_DATA_HOME') ?: $this->getHomeDir() . DIRECTORY_SEPARATOR . '.local' . DIRECTORY_SEPARATOR . 'share';
        return $path;
    }
    public function getConfigDirs()
    {
        $configDirs = getenv('XDG_CONFIG_DIRS') ? explode(':', getenv('XDG_CONFIG_DIRS')) : array('/etc/xdg');
        $paths = array_merge(array($this->getHomeConfigDir()), $configDirs);
        return $paths;
    }
    public function getDataDirs()
    {
        $dataDirs = getenv('XDG_DATA_DIRS') ? explode(':', getenv('XDG_DATA_DIRS')) : array('/usr/local/share', '/usr/share');
        $paths = array_merge(array($this->getHomeDataDir()), $dataDirs);
        return $paths;
    }
    public function getHomeCacheDir()
    {
        $path = getenv('XDG_CACHE_HOME') ?: $this->getHomeDir() . DIRECTORY_SEPARATOR . '.cache';
        return $path;
    }
    public function getRuntimeDir($strict=true)
    {
        if ($runtimeDir = getenv('XDG_RUNTIME_DIR')) {
            return $runtimeDir;
        }
        if ($strict) {
            throw new \RuntimeException('XDG_RUNTIME_DIR was not set');
        }
        $fallback = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::RUNTIME_DIR_FALLBACK . getenv('USER');
        $create = false;
        if (!is_dir($fallback)) {
            mkdir($fallback, 0700, true);
        }
        $st = lstat($fallback);
        if (!$st['mode'] & self::S_IFDIR) {
            rmdir($fallback);
            $create = true;
        } elseif ($st['uid'] != getmyuid() ||
            $st['mode'] & (self::S_IRWXG | self::S_IRWXO)
        ) {
            rmdir($fallback);
            $create = true;
        }
        if ($create) {
            mkdir($fallback, 0700, true);
        }
        return $fallback;
    }
}
