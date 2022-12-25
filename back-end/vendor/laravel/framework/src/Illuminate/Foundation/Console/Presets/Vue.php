<?php
namespace Illuminate\Foundation\Console\Presets;
use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;
class Vue extends Preset
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
        return ['vue' => '^2.5.17'] + Arr::except($packages, [
            '@babel/preset-react',
            'react',
            'react-dom',
        ]);
    }
    protected static function updateWebpackConfiguration()
    {
        copy(__DIR__.'/vue-stubs/webpack.mix.js', base_path('webpack.mix.js'));
    }
    protected static function updateComponent()
    {
        (new Filesystem)->delete(
            resource_path('js/components/Example.js')
        );
        copy(
            __DIR__.'/vue-stubs/ExampleComponent.vue',
            resource_path('js/components/ExampleComponent.vue')
        );
    }
    protected static function updateBootstrapping()
    {
        copy(__DIR__.'/vue-stubs/app.js', resource_path('js/app.js'));
    }
}
