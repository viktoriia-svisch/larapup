<?php
namespace Illuminate\Foundation\Console\Presets;
use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;
class React extends Preset
{
    public static function install()
    {
        static::ensureComponentDirectoryExists();
        static::updatePackages();
        static::updateWebpackConfiguration();
        static::updateBootstrapping();
        static::updateComponent();
        static::removeNodeModules();
    }
    protected static function updatePackageArray(array $packages)
    {
        return [
            '@babel/preset-react' => '^7.0.0',
            'react' => '^16.2.0',
            'react-dom' => '^16.2.0',
        ] + Arr::except($packages, ['vue']);
    }
    protected static function updateWebpackConfiguration()
    {
        copy(__DIR__.'/react-stubs/webpack.mix.js', base_path('webpack.mix.js'));
    }
    protected static function updateComponent()
    {
        (new Filesystem)->delete(
            resource_path('js/components/ExampleComponent.vue')
        );
        copy(
            __DIR__.'/react-stubs/Example.js',
            resource_path('js/components/Example.js')
        );
    }
    protected static function updateBootstrapping()
    {
        copy(__DIR__.'/react-stubs/app.js', resource_path('js/app.js'));
    }
}
