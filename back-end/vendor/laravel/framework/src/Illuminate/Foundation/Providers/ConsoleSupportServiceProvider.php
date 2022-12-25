<?php
namespace Illuminate\Foundation\Providers;
use Illuminate\Support\AggregateServiceProvider;
use Illuminate\Database\MigrationServiceProvider;
class ConsoleSupportServiceProvider extends AggregateServiceProvider
{
    protected $defer = true;
    protected $providers = [
        ArtisanServiceProvider::class,
        MigrationServiceProvider::class,
        ComposerServiceProvider::class,
    ];
}
