<?php
namespace Illuminate\Support\Facades;
use Illuminate\Filesystem\Filesystem;
class Storage extends Facade
{
    public static function fake($disk = null)
    {
        $disk = $disk ?: self::$app['config']->get('filesystems.default');
        (new Filesystem)->cleanDirectory(
            $root = storage_path('framework/testing/disks/'.$disk)
        );
        static::set($disk, self::createLocalDriver(['root' => $root]));
    }
    public static function persistentFake($disk = null)
    {
        $disk = $disk ?: self::$app['config']->get('filesystems.default');
        static::set($disk, self::createLocalDriver([
            'root' => storage_path('framework/testing/disks/'.$disk),
        ]));
    }
    protected static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}
