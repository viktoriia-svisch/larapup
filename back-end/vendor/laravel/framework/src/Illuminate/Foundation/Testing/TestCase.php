<?php
namespace Illuminate\Foundation\Testing;
use Mockery;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Console\Application as Artisan;
use PHPUnit\Framework\TestCase as BaseTestCase;
abstract class TestCase extends BaseTestCase
{
    use Concerns\InteractsWithContainer,
        Concerns\MakesHttpRequests,
        Concerns\InteractsWithAuthentication,
        Concerns\InteractsWithConsole,
        Concerns\InteractsWithDatabase,
        Concerns\InteractsWithExceptionHandling,
        Concerns\InteractsWithSession,
        Concerns\MocksApplicationServices;
    protected $app;
    protected $afterApplicationCreatedCallbacks = [];
    protected $beforeApplicationDestroyedCallbacks = [];
    protected $setUpHasRun = false;
    abstract public function createApplication();
    protected function setUp()
    {
        if (! $this->app) {
            $this->refreshApplication();
        }
        $this->setUpTraits();
        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            call_user_func($callback);
        }
        Facade::clearResolvedInstances();
        Model::setEventDispatcher($this->app['events']);
        $this->setUpHasRun = true;
    }
    protected function refreshApplication()
    {
        $this->app = $this->createApplication();
    }
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));
        if (isset($uses[RefreshDatabase::class])) {
            $this->refreshDatabase();
        }
        if (isset($uses[DatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }
        if (isset($uses[DatabaseTransactions::class])) {
            $this->beginDatabaseTransaction();
        }
        if (isset($uses[WithoutMiddleware::class])) {
            $this->disableMiddlewareForAllTests();
        }
        if (isset($uses[WithoutEvents::class])) {
            $this->disableEventsForAllTests();
        }
        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker();
        }
        return $uses;
    }
    protected function tearDown()
    {
        if ($this->app) {
            foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
                call_user_func($callback);
            }
            $this->app->flush();
            $this->app = null;
        }
        $this->setUpHasRun = false;
        if (property_exists($this, 'serverVariables')) {
            $this->serverVariables = [];
        }
        if (property_exists($this, 'defaultHeaders')) {
            $this->defaultHeaders = [];
        }
        if (class_exists('Mockery')) {
            if ($container = Mockery::getContainer()) {
                $this->addToAssertionCount($container->mockery_getExpectationCount());
            }
            Mockery::close();
        }
        if (class_exists(Carbon::class)) {
            Carbon::setTestNow();
        }
        $this->afterApplicationCreatedCallbacks = [];
        $this->beforeApplicationDestroyedCallbacks = [];
        Artisan::forgetBootstrappers();
    }
    public function afterApplicationCreated(callable $callback)
    {
        $this->afterApplicationCreatedCallbacks[] = $callback;
        if ($this->setUpHasRun) {
            call_user_func($callback);
        }
    }
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }
}
