<?php
namespace Illuminate\Foundation\Support\Providers;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];
    public function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }
    public function register()
    {
    }
    public function policies()
    {
        return $this->policies;
    }
}
