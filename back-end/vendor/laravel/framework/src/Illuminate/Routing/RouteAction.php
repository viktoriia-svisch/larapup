<?php
namespace Illuminate\Routing;
use LogicException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use UnexpectedValueException;
class RouteAction
{
    public static function parse($uri, $action)
    {
        if (is_null($action)) {
            return static::missingAction($uri);
        }
        if (is_callable($action)) {
            return ! is_array($action) ? ['uses' => $action] : [
                'uses' => $action[0].'@'.$action[1],
                'controller' => $action[0].'@'.$action[1],
            ];
        }
        elseif (! isset($action['uses'])) {
            $action['uses'] = static::findCallable($action);
        }
        if (is_string($action['uses']) && ! Str::contains($action['uses'], '@')) {
            $action['uses'] = static::makeInvokable($action['uses']);
        }
        return $action;
    }
    protected static function missingAction($uri)
    {
        return ['uses' => function () use ($uri) {
            throw new LogicException("Route for [{$uri}] has no action.");
        }];
    }
    protected static function findCallable(array $action)
    {
        return Arr::first($action, function ($value, $key) {
            return is_callable($value) && is_numeric($key);
        });
    }
    protected static function makeInvokable($action)
    {
        if (! method_exists($action, '__invoke')) {
            throw new UnexpectedValueException("Invalid route action: [{$action}].");
        }
        return $action.'@__invoke';
    }
}
