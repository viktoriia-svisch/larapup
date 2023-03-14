<?php
namespace Illuminate\Queue;
use ReflectionFunction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Container\Container;
class CallQueuedClosure implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $closure;
    public $deleteWhenMissingModels = true;
    public function __construct(SerializableClosure $closure)
    {
        $this->closure = $closure;
    }
    public function handle(Container $container)
    {
        $container->call($this->closure->getClosure());
    }
    public function displayName()
    {
        $reflection = new ReflectionFunction($this->closure->getClosure());
        return 'Closure ('.basename($reflection->getFileName()).':'.$reflection->getStartLine().')';
    }
}
