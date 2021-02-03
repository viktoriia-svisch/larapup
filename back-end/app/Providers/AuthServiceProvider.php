<?php
namespace App\Providers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];
    public function boot()
    {
        $this->registerPolicies();
        Auth::extend('jwt-auth', function ($app, $name, array $config) {
            $userProvider = app(TokenToUserProvider::class);
            $request = app('request');
            return new AccessTokenGuard($userProvider, $request, $config);
        });
    }
}
