<?php
namespace Illuminate\Foundation\Bootstrap;
use Illuminate\Contracts\Foundation\Application;
class RegisterProviders
{
    public function bootstrap(Application $app)
    {
        $app->registerConfiguredProviders();
    }
}
