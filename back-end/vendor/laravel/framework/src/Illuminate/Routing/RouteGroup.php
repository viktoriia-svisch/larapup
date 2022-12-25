<?php
namespace Illuminate\Routing;
use Illuminate\Support\Arr;
class RouteGroup
{
    public static function merge($new, $old)
    {
        if (isset($new['domain'])) {
            unset($old['domain']);
        }
        $new = array_merge(static::formatAs($new, $old), [
            'namespace' => static::formatNamespace($new, $old),
            'prefix' => static::formatPrefix($new, $old),
            'where' => static::formatWhere($new, $old),
        ]);
        return array_merge_recursive(Arr::except(
            $old, ['namespace', 'prefix', 'where', 'as']
        ), $new);
    }
    protected static function formatNamespace($new, $old)
    {
        if (isset($new['namespace'])) {
            return isset($old['namespace']) && strpos($new['namespace'], '\\') !== 0
                    ? trim($old['namespace'], '\\').'\\'.trim($new['namespace'], '\\')
                    : trim($new['namespace'], '\\');
        }
        return $old['namespace'] ?? null;
    }
    protected static function formatPrefix($new, $old)
    {
        $old = $old['prefix'] ?? null;
        return isset($new['prefix']) ? trim($old, '/').'/'.trim($new['prefix'], '/') : $old;
    }
    protected static function formatWhere($new, $old)
    {
        return array_merge(
            $old['where'] ?? [],
            $new['where'] ?? []
        );
    }
    protected static function formatAs($new, $old)
    {
        if (isset($old['as'])) {
            $new['as'] = $old['as'].($new['as'] ?? '');
        }
        return $new;
    }
}
