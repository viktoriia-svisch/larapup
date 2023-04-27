<?php
namespace Illuminate\Foundation\Console;
use Closure;
use Exception;
use Throwable;
use ReflectionClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Console\Application as Artisan;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Symfony\Component\Debug\Exception\FatalThrowableError;
class Kernel implements KernelContract
{
    protected $app;
    protected $events;
    protected $artisan;
    protected $commands = [];
    protected $commandsLoaded = false;
    protected $bootstrappers = [
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];
    public function __construct(Application $app, Dispatcher $events)
    {
        if (! defined('ARTISAN_BINARY')) {
            define('ARTISAN_BINARY', 'artisan');
        }
        $this->app = $app;
        $this->events = $events;
        $this->app->booted(function () {
            $this->defineConsoleSchedule();
        });
    }
    protected function defineConsoleSchedule()
    {
        $this->app->singleton(Schedule::class, function () {
            return new Schedule;
        });
        $schedule = $this->app->make(Schedule::class);
        $this->schedule($schedule);
    }
    public function handle($input, $output = null)
    {
        try {
            $this->bootstrap();
            return $this->getArtisan()->run($input, $output);
        } catch (Exception $e) {
            $this->reportException($e);
            $this->renderException($output, $e);
            return 1;
        } catch (Throwable $e) {
            $e = new FatalThrowableError($e);
            $this->reportException($e);
            $this->renderException($output, $e);
            return 1;
        }
    }
    public function terminate($input, $status)
    {
        $this->app->terminate();
    }
    protected function schedule(Schedule $schedule)
    {
    }
    protected function commands()
    {
    }
    public function command($signature, Closure $callback)
    {
        $command = new ClosureCommand($signature, $callback);
        Artisan::starting(function ($artisan) use ($command) {
            $artisan->add($command);
        });
        return $command;
    }
    protected function load($paths)
    {
        $paths = array_unique(Arr::wrap($paths));
        $paths = array_filter($paths, function ($path) {
            return is_dir($path);
        });
        if (empty($paths)) {
            return;
        }
        $namespace = $this->app->getNamespace();
        foreach ((new Finder)->in($paths)->files() as $command) {
            $command = $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($command->getPathname(), app_path().DIRECTORY_SEPARATOR)
            );
            if (is_subclass_of($command, Command::class) &&
                ! (new ReflectionClass($command))->isAbstract()) {
                Artisan::starting(function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }
    public function registerCommand($command)
    {
        $this->getArtisan()->add($command);
    }
    public function call($command, array $parameters = [], $outputBuffer = null)
    {
        $this->bootstrap();
        return $this->getArtisan()->call($command, $parameters, $outputBuffer);
    }
    public function queue($command, array $parameters = [])
    {
        return QueuedCommand::dispatch(func_get_args());
    }
    public function all()
    {
        $this->bootstrap();
        return $this->getArtisan()->all();
    }
    public function output()
    {
        $this->bootstrap();
        return $this->getArtisan()->output();
    }
    public function bootstrap()
    {
        if (! $this->app->hasBeenBootstrapped()) {
            $this->app->bootstrapWith($this->bootstrappers());
        }
        $this->app->loadDeferredProviders();
        if (! $this->commandsLoaded) {
            $this->commands();
            $this->commandsLoaded = true;
        }
    }
    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            return $this->artisan = (new Artisan($this->app, $this->events, $this->app->version()))
                                ->resolveCommands($this->commands);
        }
        return $this->artisan;
    }
    public function setArtisan($artisan)
    {
        $this->artisan = $artisan;
    }
    protected function bootstrappers()
    {
        return $this->bootstrappers;
    }
    protected function reportException(Exception $e)
    {
        $this->app[ExceptionHandler::class]->report($e);
    }
    protected function renderException($output, Exception $e)
    {
        $this->app[ExceptionHandler::class]->renderForConsole($output, $e);
    }
}
