<?php
namespace BeyondCode\DumpServer;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\Server\DumpServer;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
class DumpServerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('debug-server.php'),
            ], 'config');
        }
    }
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'debug-server');
        $this->app->bind('command.dumpserver', DumpServerCommand::class);
        $this->commands([
            'command.dumpserver',
        ]);
        $host = $this->app['config']->get('debug-server.host');
        $this->app->when(DumpServer::class)->needs('$host')->give($host);
        $connection = new Connection($host, [
            'request' => new RequestContextProvider($this->app['request']),
            'source' => new SourceContextProvider('utf-8', base_path()),
        ]);
        VarDumper::setHandler(function ($var) use ($connection) {
            (new Dumper($connection))->dump($var);
        });
    }
}
