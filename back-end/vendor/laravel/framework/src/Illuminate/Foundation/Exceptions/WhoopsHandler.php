<?php
namespace Illuminate\Foundation\Exceptions;
use Illuminate\Support\Arr;
use Illuminate\Filesystem\Filesystem;
use Whoops\Handler\PrettyPageHandler;
class WhoopsHandler
{
    public function forDebug()
    {
        return tap(new PrettyPageHandler, function ($handler) {
            $handler->handleUnconditionally(true);
            $this->registerApplicationPaths($handler)
                 ->registerBlacklist($handler)
                 ->registerEditor($handler);
        });
    }
    protected function registerApplicationPaths($handler)
    {
        $handler->setApplicationPaths(
            array_flip($this->directoriesExceptVendor())
        );
        return $this;
    }
    protected function directoriesExceptVendor()
    {
        return Arr::except(
            array_flip((new Filesystem)->directories(base_path())),
            [base_path('vendor')]
        );
    }
    protected function registerBlacklist($handler)
    {
        foreach (config('app.debug_blacklist', []) as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }
        return $this;
    }
    protected function registerEditor($handler)
    {
        if (config('app.editor', false)) {
            $handler->setEditor(config('app.editor'));
        }
        return $this;
    }
}
