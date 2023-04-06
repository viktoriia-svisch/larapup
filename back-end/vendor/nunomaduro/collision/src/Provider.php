<?php
namespace NunoMaduro\Collision;
use Whoops\Run;
use Whoops\RunInterface;
use NunoMaduro\Collision\Contracts\Handler as HandlerContract;
use NunoMaduro\Collision\Contracts\Provider as ProviderContract;
class Provider implements ProviderContract
{
    protected $run;
    protected $handler;
    public function __construct(RunInterface $run = null, HandlerContract $handler = null)
    {
        $this->run = $run ?: new Run;
        $this->handler = $handler ?: new Handler;
    }
    public function register(): ProviderContract
    {
        $this->run->pushHandler($this->handler)
            ->register();
        return $this;
    }
    public function getHandler(): HandlerContract
    {
        return $this->handler;
    }
}
