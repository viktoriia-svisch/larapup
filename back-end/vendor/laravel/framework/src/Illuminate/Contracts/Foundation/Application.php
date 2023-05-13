<?php
namespace Illuminate\Contracts\Foundation;
use Illuminate\Contracts\Container\Container;
interface Application extends Container
{
    public function version();
    public function basePath();
    public function environment();
    public function runningInConsole();
    public function runningUnitTests();
    public function isDownForMaintenance();
    public function registerConfiguredProviders();
    public function register($provider, $force = false);
    public function registerDeferredProvider($provider, $service = null);
    public function boot();
    public function booting($callback);
    public function booted($callback);
    public function getCachedServicesPath();
    public function getCachedPackagesPath();
}
