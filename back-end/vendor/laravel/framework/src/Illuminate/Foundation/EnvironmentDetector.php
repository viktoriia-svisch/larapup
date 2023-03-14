<?php
namespace Illuminate\Foundation;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
class EnvironmentDetector
{
    public function detect(Closure $callback, $consoleArgs = null)
    {
        if ($consoleArgs) {
            return $this->detectConsoleEnvironment($callback, $consoleArgs);
        }
        return $this->detectWebEnvironment($callback);
    }
    protected function detectWebEnvironment(Closure $callback)
    {
        return call_user_func($callback);
    }
    protected function detectConsoleEnvironment(Closure $callback, array $args)
    {
        if (! is_null($value = $this->getEnvironmentArgument($args))) {
            return head(array_slice(explode('=', $value), 1));
        }
        return $this->detectWebEnvironment($callback);
    }
    protected function getEnvironmentArgument(array $args)
    {
        return Arr::first($args, function ($value) {
            return Str::startsWith($value, '--env');
        });
    }
}
