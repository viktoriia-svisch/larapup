<?php
namespace Illuminate\Foundation\Bootstrap;
use Illuminate\Http\Request;
use Illuminate\Contracts\Foundation\Application;
class SetRequestForConsole
{
    public function bootstrap(Application $app)
    {
        $uri = $app->make('config')->get('app.url', 'http:
        $components = parse_url($uri);
        $server = $_SERVER;
        if (isset($components['path'])) {
            $server = array_merge($server, [
                'SCRIPT_FILENAME' => $components['path'],
                'SCRIPT_NAME' => $components['path'],
            ]);
        }
        $app->instance('request', Request::create(
            $uri, 'GET', [], [], [], $server
        ));
    }
}
