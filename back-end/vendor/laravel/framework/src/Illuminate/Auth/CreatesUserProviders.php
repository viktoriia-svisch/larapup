<?php
namespace Illuminate\Auth;
use InvalidArgumentException;
trait CreatesUserProviders
{
    protected $customProviderCreators = [];
    public function createUserProvider($provider = null)
    {
        if (is_null($config = $this->getProviderConfiguration($provider))) {
            return;
        }
        if (isset($this->customProviderCreators[$driver = ($config['driver'] ?? null)])) {
            return call_user_func(
                $this->customProviderCreators[$driver], $this->app, $config
            );
        }
        switch ($driver) {
            case 'database':
                return $this->createDatabaseProvider($config);
            case 'eloquent':
                return $this->createEloquentProvider($config);
            default:
                throw new InvalidArgumentException(
                    "Authentication user provider [{$driver}] is not defined."
                );
        }
    }
    protected function getProviderConfiguration($provider)
    {
        if ($provider = $provider ?: $this->getDefaultUserProvider()) {
            return $this->app['config']['auth.providers.'.$provider];
        }
    }
    protected function createDatabaseProvider($config)
    {
        $connection = $this->app['db']->connection();
        return new DatabaseUserProvider($connection, $this->app['hash'], $config['table']);
    }
    protected function createEloquentProvider($config)
    {
        return new EloquentUserProvider($this->app['hash'], $config['model']);
    }
    public function getDefaultUserProvider()
    {
        return $this->app['config']['auth.defaults.provider'];
    }
}
