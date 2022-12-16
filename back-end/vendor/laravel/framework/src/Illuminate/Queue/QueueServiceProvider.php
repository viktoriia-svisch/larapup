<?php
namespace Illuminate\Queue;
use Illuminate\Support\Str;
use Opis\Closure\SerializableClosure;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Queue\Connectors\NullConnector;
use Illuminate\Queue\Connectors\SyncConnector;
use Illuminate\Queue\Connectors\RedisConnector;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Queue\Connectors\DatabaseConnector;
use Illuminate\Queue\Failed\NullFailedJobProvider;
use Illuminate\Queue\Connectors\BeanstalkdConnector;
use Illuminate\Queue\Failed\DatabaseFailedJobProvider;
class QueueServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->registerManager();
        $this->registerConnection();
        $this->registerWorker();
        $this->registerListener();
        $this->registerFailedJobServices();
        $this->registerOpisSecurityKey();
    }
    protected function registerManager()
    {
        $this->app->singleton('queue', function ($app) {
            return tap(new QueueManager($app), function ($manager) {
                $this->registerConnectors($manager);
            });
        });
    }
    protected function registerConnection()
    {
        $this->app->singleton('queue.connection', function ($app) {
            return $app['queue']->connection();
        });
    }
    public function registerConnectors($manager)
    {
        foreach (['Null', 'Sync', 'Database', 'Redis', 'Beanstalkd', 'Sqs'] as $connector) {
            $this->{"register{$connector}Connector"}($manager);
        }
    }
    protected function registerNullConnector($manager)
    {
        $manager->addConnector('null', function () {
            return new NullConnector;
        });
    }
    protected function registerSyncConnector($manager)
    {
        $manager->addConnector('sync', function () {
            return new SyncConnector;
        });
    }
    protected function registerDatabaseConnector($manager)
    {
        $manager->addConnector('database', function () {
            return new DatabaseConnector($this->app['db']);
        });
    }
    protected function registerRedisConnector($manager)
    {
        $manager->addConnector('redis', function () {
            return new RedisConnector($this->app['redis']);
        });
    }
    protected function registerBeanstalkdConnector($manager)
    {
        $manager->addConnector('beanstalkd', function () {
            return new BeanstalkdConnector;
        });
    }
    protected function registerSqsConnector($manager)
    {
        $manager->addConnector('sqs', function () {
            return new SqsConnector;
        });
    }
    protected function registerWorker()
    {
        $this->app->singleton('queue.worker', function () {
            return new Worker(
                $this->app['queue'], $this->app['events'], $this->app[ExceptionHandler::class]
            );
        });
    }
    protected function registerListener()
    {
        $this->app->singleton('queue.listener', function () {
            return new Listener($this->app->basePath());
        });
    }
    protected function registerFailedJobServices()
    {
        $this->app->singleton('queue.failer', function () {
            $config = $this->app['config']['queue.failed'];
            return isset($config['table'])
                        ? $this->databaseFailedJobProvider($config)
                        : new NullFailedJobProvider;
        });
    }
    protected function databaseFailedJobProvider($config)
    {
        return new DatabaseFailedJobProvider(
            $this->app['db'], $config['database'], $config['table']
        );
    }
    protected function registerOpisSecurityKey()
    {
        if (Str::startsWith($key = $this->app['config']->get('app.key'), 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        SerializableClosure::setSecretKey($key);
    }
    public function provides()
    {
        return [
            'queue', 'queue.worker', 'queue.listener',
            'queue.failer', 'queue.connection',
        ];
    }
}
