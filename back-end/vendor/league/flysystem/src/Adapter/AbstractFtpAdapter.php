<?php
namespace League\Flysystem\Adapter;
use DateTime;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use League\Flysystem\NotSupportedException;
use League\Flysystem\SafeStorage;
use RuntimeException;
abstract class AbstractFtpAdapter extends AbstractAdapter
{
    protected $connection;
    protected $host;
    protected $port = 21;
    protected $ssl = false;
    protected $timeout = 90;
    protected $passive = true;
    protected $separator = '/';
    protected $root;
    protected $permPublic = 0744;
    protected $permPrivate = 0700;
    protected $configurable = [];
    protected $systemType;
    protected $safeStorage;
    public function __construct(array $config)
    {
        $this->safeStorage = new SafeStorage();
        $this->setConfig($config);
    }
    public function setConfig(array $config)
    {
        foreach ($this->configurable as $setting) {
            if ( ! isset($config[$setting])) {
                continue;
            }
            $method = 'set' . ucfirst($setting);
            if (method_exists($this, $method)) {
                $this->$method($config[$setting]);
            }
        }
        return $this;
    }
    public function getHost()
    {
        return $this->host;
    }
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }
    public function setPermPublic($permPublic)
    {
        $this->permPublic = $permPublic;
        return $this;
    }
    public function setPermPrivate($permPrivate)
    {
        $this->permPrivate = $permPrivate;
        return $this;
    }
    public function getPort()
    {
        return $this->port;
    }
    public function getRoot()
    {
        return $this->root;
    }
    public function setPort($port)
    {
        $this->port = (int) $port;
        return $this;
    }
    public function setRoot($root)
    {
        $this->root = rtrim($root, '\\/') . $this->separator;
        return $this;
    }
    public function getUsername()
    {
        $username = $this->safeStorage->retrieveSafely('username');
        return $username !== null ? $username : 'anonymous';
    }
    public function setUsername($username)
    {
        $this->safeStorage->storeSafely('username', $username);
        return $this;
    }
    public function getPassword()
    {
        return $this->safeStorage->retrieveSafely('password');
    }
    public function setPassword($password)
    {
        $this->safeStorage->storeSafely('password', $password);
        return $this;
    }
    public function getTimeout()
    {
        return $this->timeout;
    }
    public function setTimeout($timeout)
    {
        $this->timeout = (int) $timeout;
        return $this;
    }
    public function getSystemType()
    {
        return $this->systemType;
    }
    public function setSystemType($systemType)
    {
        $this->systemType = strtolower($systemType);
        return $this;
    }
    public function listContents($directory = '', $recursive = false)
    {
        return $this->listDirectoryContents($directory, $recursive);
    }
    abstract protected function listDirectoryContents($directory, $recursive = false);
    protected function normalizeListing(array $listing, $prefix = '')
    {
        $base = $prefix;
        $result = [];
        $listing = $this->removeDotDirectories($listing);
        while ($item = array_shift($listing)) {
            if (preg_match('#^.*:$#', $item)) {
                $base = preg_replace('~^\.
    protected function sortListing(array $result)
    {
        $compare = function ($one, $two) {
            return strnatcmp($one['path'], $two['path']);
        };
        usort($result, $compare);
        return $result;
    }
    protected function normalizeObject($item, $base)
    {
        $systemType = $this->systemType ?: $this->detectSystemType($item);
        if ($systemType === 'unix') {
            return $this->normalizeUnixObject($item, $base);
        } elseif ($systemType === 'windows') {
            return $this->normalizeWindowsObject($item, $base);
        }
        throw NotSupportedException::forFtpSystemType($systemType);
    }
    protected function normalizeUnixObject($item, $base)
    {
        $item = preg_replace('#\s+#', ' ', trim($item), 7);
        if (count(explode(' ', $item, 9)) !== 9) {
            throw new RuntimeException("Metadata can't be parsed from item '$item' , not enough parts.");
        }
        list($permissions, , , , $size, , , , $name) = explode(' ', $item, 9);
        $type = $this->detectType($permissions);
        $path = $base === '' ? $name : $base . $this->separator . $name;
        if ($type === 'dir') {
            return compact('type', 'path');
        }
        $permissions = $this->normalizePermissions($permissions);
        $visibility = $permissions & 0044 ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;
        $size = (int) $size;
        return compact('type', 'path', 'visibility', 'size');
    }
    protected function normalizeWindowsObject($item, $base)
    {
        $item = preg_replace('#\s+#', ' ', trim($item), 3);
        if (count(explode(' ', $item, 4)) !== 4) {
            throw new RuntimeException("Metadata can't be parsed from item '$item' , not enough parts.");
        }
        list($date, $time, $size, $name) = explode(' ', $item, 4);
        $path = $base === '' ? $name : $base . $this->separator . $name;
        $format = strlen($date) === 8 ? 'm-d-yH:iA' : 'Y-m-dH:i';
        $dt = DateTime::createFromFormat($format, $date . $time);
        $timestamp = $dt ? $dt->getTimestamp() : (int) strtotime("$date $time");
        if ($size === '<DIR>') {
            $type = 'dir';
            return compact('type', 'path', 'timestamp');
        }
        $type = 'file';
        $visibility = AdapterInterface::VISIBILITY_PUBLIC;
        $size = (int) $size;
        return compact('type', 'path', 'visibility', 'size', 'timestamp');
    }
    protected function detectSystemType($item)
    {
        return preg_match('/^[0-9]{2,4}-[0-9]{2}-[0-9]{2}/', $item) ? 'windows' : 'unix';
    }
    protected function detectType($permissions)
    {
        return substr($permissions, 0, 1) === 'd' ? 'dir' : 'file';
    }
    protected function normalizePermissions($permissions)
    {
        $permissions = substr($permissions, 1);
        $map = ['-' => '0', 'r' => '4', 'w' => '2', 'x' => '1'];
        $permissions = strtr($permissions, $map);
        $parts = str_split($permissions, 3);
        $mapper = function ($part) {
            return array_sum(str_split($part));
        };
        return octdec(implode('', array_map($mapper, $parts)));
    }
    public function removeDotDirectories(array $list)
    {
        $filter = function ($line) {
            return $line !== '' && ! preg_match('#.* \.(\.)?$|^total#', $line);
        };
        return array_filter($list, $filter);
    }
    public function has($path)
    {
        return $this->getMetadata($path);
    }
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }
    public function getVisibility($path)
    {
        return $this->getMetadata($path);
    }
    public function ensureDirectory($dirname)
    {
        $dirname = (string) $dirname;
        if ($dirname !== '' && ! $this->has($dirname)) {
            $this->createDir($dirname, new Config());
        }
    }
    public function getConnection()
    {
        $tries = 0;
        while ( ! $this->isConnected() && $tries < 3) {
            $tries++;
            $this->disconnect();
            $this->connect();
        }
        return $this->connection;
    }
    public function getPermPublic()
    {
        return $this->permPublic;
    }
    public function getPermPrivate()
    {
        return $this->permPrivate;
    }
    public function __destruct()
    {
        $this->disconnect();
    }
    abstract public function connect();
    abstract public function disconnect();
    abstract public function isConnected();
}
