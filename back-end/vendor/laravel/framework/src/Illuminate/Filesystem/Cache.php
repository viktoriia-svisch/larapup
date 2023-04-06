<?php
namespace Illuminate\Filesystem;
use Illuminate\Contracts\Cache\Repository;
use League\Flysystem\Cached\Storage\AbstractCache;
class Cache extends AbstractCache
{
    protected $repository;
    protected $key;
    protected $expire;
    public function __construct(Repository $repository, $key = 'flysystem', $expire = null)
    {
        $this->key = $key;
        $this->repository = $repository;
        if (! is_null($expire)) {
            $this->expire = (int) ceil($expire / 60);
        }
    }
    public function load()
    {
        $contents = $this->repository->get($this->key);
        if (! is_null($contents)) {
            $this->setFromStorage($contents);
        }
    }
    public function save()
    {
        $contents = $this->getForStorage();
        if (! is_null($this->expire)) {
            $this->repository->put($this->key, $contents, $this->expire);
        } else {
            $this->repository->forever($this->key, $contents);
        }
    }
}
