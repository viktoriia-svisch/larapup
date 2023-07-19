<?php
namespace Illuminate\Foundation\Console\Presets;
use Illuminate\Filesystem\Filesystem;
class None extends Preset
{
    public static function install()
    {
        static::updatePackages();
        static::updateBootstrapping();
        tap(new Filesystem, function ($filesystem) {
            $filesystem->deleteDirectory(resource_path('js/components'));
            $filesystem->delete(resource_path('sass/_variables.scss'));
            $filesystem->deleteDirectory(base_path('node_modules'));
            $filesystem->deleteDirectory(public_path('css'));
            $filesystem->deleteDirectory(public_path('js'));
        });
    }
    protected static function updatePackageArray(array $packages)
    {
        unset(
            $packages['bootstrap'],
            $packages['jquery'],
            $packages['popper.js'],
            $packages['vue'],
            $packages['@babel/preset-react'],
            $packages['react'],
            $packages['react-dom']
        );
        return $packages;
    }
    protected static function updateBootstrapping()
    {
        file_put_contents(resource_path('sass/app.scss'), ''.PHP_EOL);
        copy(__DIR__.'/none-stubs/app.js', resource_path('js/app.js'));
        copy(__DIR__.'/none-stubs/bootstrap.js', resource_path('js/bootstrap.js'));
    }
}