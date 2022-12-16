<?php
namespace Illuminate\Filesystem;
use Closure;
use Aws\S3\S3Client;
use OpenCloud\Rackspace;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Adapter\Ftp as FtpAdapter;
use League\Flysystem\Rackspace\RackspaceAdapter;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AwsS3v3\AwsS3Adapter as S3Adapter;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;
use Illuminate\Contracts\Filesystem\Factory as FactoryContract;
class FilesystemManager implements FactoryContract
{
    protected $app;
    protected $disks = [];
    protected $customCreators = [];
    public function __construct($app)
    {
        $this->app = $app;
    }
    public function drive($name = null)
    {
        return $this->disk($name);
    }
    public function disk($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();
        return $this->disks[$name] = $this->get($name);
    }
    public function cloud()
    {
        $name = $this->getDefaultCloudDriver();
        return $this->disks[$name] = $this->get($name);
    }
    protected function get($name)
    {
        return $this->disks[$name] ?? $this->resolve($name);
    }
    protected function resolve($name)
    {
        $config = $this->getConfig($name);
        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        }
        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';
        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }
    protected function callCustomCreator(array $config)
    {
        $driver = $this->customCreators[$config['driver']]($this->app, $config);
        if ($driver instanceof FilesystemInterface) {
            return $this->adapt($driver);
        }
        return $driver;
    }
    public function createLocalDriver(array $config)
    {
        $permissions = $config['permissions'] ?? [];
        $links = ($config['links'] ?? null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;
        return $this->adapt($this->createFlysystem(new LocalAdapter(
            $config['root'], LOCK_EX, $links, $permissions
        ), $config));
    }
    public function createFtpDriver(array $config)
    {
        return $this->adapt($this->createFlysystem(
            new FtpAdapter($config), $config
        ));
    }
    public function createSftpDriver(array $config)
    {
        return $this->adapt($this->createFlysystem(
            new SftpAdapter($config), $config
        ));
    }
    public function createS3Driver(array $config)
    {
        $s3Config = $this->formatS3Config($config);
        $root = $s3Config['root'] ?? null;
        $options = $config['options'] ?? [];
        return $this->adapt($this->createFlysystem(
            new S3Adapter(new S3Client($s3Config), $s3Config['bucket'], $root, $options), $config
        ));
    }
    protected function formatS3Config(array $config)
    {
        $config += ['version' => 'latest'];
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }
        return $config;
    }
    public function createRackspaceDriver(array $config)
    {
        $client = new Rackspace($config['endpoint'], [
            'username' => $config['username'], 'apiKey' => $config['key'],
        ], $config['options'] ?? []);
        $root = $config['root'] ?? null;
        return $this->adapt($this->createFlysystem(
            new RackspaceAdapter($this->getRackspaceContainer($client, $config), $root), $config
        ));
    }
    protected function getRackspaceContainer(Rackspace $client, array $config)
    {
        $urlType = $config['url_type'] ?? null;
        $store = $client->objectStoreService('cloudFiles', $config['region'], $urlType);
        return $store->getContainer($config['container']);
    }
    protected function createFlysystem(AdapterInterface $adapter, array $config)
    {
        $cache = Arr::pull($config, 'cache');
        $config = Arr::only($config, ['visibility', 'disable_asserts', 'url']);
        if ($cache) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
        }
        return new Flysystem($adapter, count($config) > 0 ? $config : null);
    }
    protected function createCacheStore($config)
    {
        if ($config === true) {
            return new MemoryStore;
        }
        return new Cache(
            $this->app['cache']->store($config['store']),
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }
    protected function adapt(FilesystemInterface $filesystem)
    {
        return new FilesystemAdapter($filesystem);
    }
    public function set($name, $disk)
    {
        $this->disks[$name] = $disk;
        return $this;
    }
    protected function getConfig($name)
    {
        return $this->app['config']["filesystems.disks.{$name}"];
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['filesystems.default'];
    }
    public function getDefaultCloudDriver()
    {
        return $this->app['config']['filesystems.cloud'];
    }
    public function forgetDisk($disk)
    {
        foreach ((array) $disk as $diskName) {
            unset($this->disks[$diskName]);
        }
        return $this;
    }
    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }
    public function __call($method, $parameters)
    {
        return $this->disk()->$method(...$parameters);
    }
}
