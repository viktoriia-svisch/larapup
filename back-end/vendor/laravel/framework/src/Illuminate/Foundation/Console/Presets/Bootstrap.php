<?php
namespace Illuminate\Foundation\Console\Presets;
class Bootstrap extends Preset
{
    public static function install()
    {
        static::updatePackages();
        static::updateSass();
        static::removeNodeModules();
    }
    protected static function updatePackageArray(array $packages)
    {
        return [
            'bootstrap' => '^4.0.0',
            'jquery' => '^3.2',
            'popper.js' => '^1.12',
        ] + $packages;
    }
    protected static function updateSass()
    {
        copy(__DIR__.'/bootstrap-stubs/_variables.scss', resource_path('sass/_variables.scss'));
        copy(__DIR__.'/bootstrap-stubs/app.scss', resource_path('sass/app.scss'));
    }
}
