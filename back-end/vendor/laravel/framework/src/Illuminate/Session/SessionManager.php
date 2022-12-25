<?php
namespace Illuminate\Session;
use Illuminate\Support\Manager;
class SessionManager extends Manager
{
    protected function callCustomCreator($driver)
    {
        return $this->buildSession(parent::callCustomCreator($driver));
    }
    protected function createArrayDriver()
    {
        return $this->buildSession(new NullSessionHandler);
    }
    protected function createCookieDriver()
    {
        return $this->buildSession(new CookieSessionHandler(
            $this->app['cookie'], $this->app['config']['session.lifetime']
        ));
    }
    protected function createFileDriver()
    {
        return $this->createNativeDriver();
    }
    protected function createNativeDriver()
    {
        $lifetime = $this->app['config']['session.lifetime'];
        return $this->buildSession(new FileSessionHandler(
            $this->app['files'], $this->app['config']['session.files'], $lifetime
        ));
    }
    protected function createDatabaseDriver()
    {
        $table = $this->app['config']['session.table'];
        $lifetime = $this->app['config']['session.lifetime'];
        return $this->buildSession(new DatabaseSessionHandler(
            $this->getDatabaseConnection(), $table, $lifetime, $this->app
        ));
    }
    protected function getDatabaseConnection()
    {
        $connection = $this->app['config']['session.connection'];
        return $this->app['db']->connection($connection);
    }
    protected function createApcDriver()
    {
        return $this->createCacheBased('apc');
    }
    protected function createMemcachedDriver()
    {
        return $this->createCacheBased('memcached');
    }
    protected function createRedisDriver()
    {
        $handler = $this->createCacheHandler('redis');
        $handler->getCache()->getStore()->setConnection(
            $this->app['config']['session.connection']
        );
        return $this->buildSession($handler);
    }
    protected function createCacheBased($driver)
    {
        return $this->buildSession($this->createCacheHandler($driver));
    }
    protected function createCacheHandler($driver)
    {
        $store = $this->app['config']->get('session.store') ?: $driver;
        return new CacheBasedSessionHandler(
            clone $this->app['cache']->store($store),
            $this->app['config']['session.lifetime']
        );
    }
    protected function buildSession($handler)
    {
        if ($this->app['config']['session.encrypt']) {
            return $this->buildEncryptedSession($handler);
        }
        return new Store($this->app['config']['session.cookie'], $handler);
    }
    protected function buildEncryptedSession($handler)
    {
        return new EncryptedStore(
            $this->app['config']['session.cookie'], $handler, $this->app['encrypter']
        );
    }
    public function getSessionConfig()
    {
        return $this->app['config']['session'];
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['session.driver'];
    }
    public function setDefaultDriver($name)
    {
        $this->app['config']['session.driver'] = $name;
    }
}
