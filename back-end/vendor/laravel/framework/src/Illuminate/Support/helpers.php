<?php
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Optional;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HigherOrderTapProxy;
if (! function_exists('append_config')) {
    function append_config(array $array)
    {
        $start = 9999;
        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $start++;
                $array[$start] = Arr::pull($array, $key);
            }
        }
        return $array;
    }
}
if (! function_exists('array_add')) {
    function array_add($array, $key, $value)
    {
        return Arr::add($array, $key, $value);
    }
}
if (! function_exists('array_collapse')) {
    function array_collapse($array)
    {
        return Arr::collapse($array);
    }
}
if (! function_exists('array_divide')) {
    function array_divide($array)
    {
        return Arr::divide($array);
    }
}
if (! function_exists('array_dot')) {
    function array_dot($array, $prepend = '')
    {
        return Arr::dot($array, $prepend);
    }
}
if (! function_exists('array_except')) {
    function array_except($array, $keys)
    {
        return Arr::except($array, $keys);
    }
}
if (! function_exists('array_first')) {
    function array_first($array, callable $callback = null, $default = null)
    {
        return Arr::first($array, $callback, $default);
    }
}
if (! function_exists('array_flatten')) {
    function array_flatten($array, $depth = INF)
    {
        return Arr::flatten($array, $depth);
    }
}
if (! function_exists('array_forget')) {
    function array_forget(&$array, $keys)
    {
        return Arr::forget($array, $keys);
    }
}
if (! function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}
if (! function_exists('array_has')) {
    function array_has($array, $keys)
    {
        return Arr::has($array, $keys);
    }
}
if (! function_exists('array_last')) {
    function array_last($array, callable $callback = null, $default = null)
    {
        return Arr::last($array, $callback, $default);
    }
}
if (! function_exists('array_only')) {
    function array_only($array, $keys)
    {
        return Arr::only($array, $keys);
    }
}
if (! function_exists('array_pluck')) {
    function array_pluck($array, $value, $key = null)
    {
        return Arr::pluck($array, $value, $key);
    }
}
if (! function_exists('array_prepend')) {
    function array_prepend($array, $value, $key = null)
    {
        return Arr::prepend($array, $value, $key);
    }
}
if (! function_exists('array_pull')) {
    function array_pull(&$array, $key, $default = null)
    {
        return Arr::pull($array, $key, $default);
    }
}
if (! function_exists('array_random')) {
    function array_random($array, $num = null)
    {
        return Arr::random($array, $num);
    }
}
if (! function_exists('array_set')) {
    function array_set(&$array, $key, $value)
    {
        return Arr::set($array, $key, $value);
    }
}
if (! function_exists('array_sort')) {
    function array_sort($array, $callback = null)
    {
        return Arr::sort($array, $callback);
    }
}
if (! function_exists('array_sort_recursive')) {
    function array_sort_recursive($array)
    {
        return Arr::sortRecursive($array);
    }
}
if (! function_exists('array_where')) {
    function array_where($array, callable $callback)
    {
        return Arr::where($array, $callback);
    }
}
if (! function_exists('array_wrap')) {
    function array_wrap($value)
    {
        return Arr::wrap($value);
    }
}
if (! function_exists('blank')) {
    function blank($value)
    {
        if (is_null($value)) {
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }
        if (is_numeric($value) || is_bool($value)) {
            return false;
        }
        if ($value instanceof Countable) {
            return count($value) === 0;
        }
        return empty($value);
    }
}
if (! function_exists('camel_case')) {
    function camel_case($value)
    {
        return Str::camel($value);
    }
}
if (! function_exists('class_basename')) {
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}
if (! function_exists('class_uses_recursive')) {
    function class_uses_recursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $results = [];
        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }
        return array_unique($results);
    }
}
if (! function_exists('collect')) {
    function collect($value = null)
    {
        return new Collection($value);
    }
}
if (! function_exists('data_fill')) {
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}
if (! function_exists('data_get')) {
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }
        $key = is_array($key) ? $key : explode('.', $key);
        while (! is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (! is_array($target)) {
                    return value($default);
                }
                $result = [];
                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }
                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }
            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }
        return $target;
    }
}
if (! function_exists('data_set')) {
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);
        if (($segment = array_shift($segments)) === '*') {
            if (! Arr::accessible($target)) {
                $target = [];
            }
            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (! Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (! isset($target->{$segment})) {
                    $target->{$segment} = [];
                }
                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || ! isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];
            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }
        return $target;
    }
}
if (! function_exists('e')) {
    function e($value, $doubleEncode = true)
    {
        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}
if (! function_exists('ends_with')) {
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}
if (! function_exists('env')) {
    function env($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return value($default);
        }
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return;
        }
        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }
        return $value;
    }
}
if (! function_exists('filled')) {
    function filled($value)
    {
        return ! blank($value);
    }
}
if (! function_exists('head')) {
    function head($array)
    {
        return reset($array);
    }
}
if (! function_exists('kebab_case')) {
    function kebab_case($value)
    {
        return Str::kebab($value);
    }
}
if (! function_exists('last')) {
    function last($array)
    {
        return end($array);
    }
}
if (! function_exists('object_get')) {
    function object_get($object, $key, $default = null)
    {
        if (is_null($key) || trim($key) == '') {
            return $object;
        }
        foreach (explode('.', $key) as $segment) {
            if (! is_object($object) || ! isset($object->{$segment})) {
                return value($default);
            }
            $object = $object->{$segment};
        }
        return $object;
    }
}
if (! function_exists('optional')) {
    function optional($value = null, callable $callback = null)
    {
        if (is_null($callback)) {
            return new Optional($value);
        } elseif (! is_null($value)) {
            return $callback($value);
        }
    }
}
if (! function_exists('preg_replace_array')) {
    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}
if (! function_exists('retry')) {
    function retry($times, callable $callback, $sleep = 0)
    {
        $times--;
        beginning:
        try {
            return $callback();
        } catch (Exception $e) {
            if (! $times) {
                throw $e;
            }
            $times--;
            if ($sleep) {
                usleep($sleep * 1000);
            }
            goto beginning;
        }
    }
}
if (! function_exists('snake_case')) {
    function snake_case($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }
}
if (! function_exists('starts_with')) {
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}
if (! function_exists('str_after')) {
    function str_after($subject, $search)
    {
        return Str::after($subject, $search);
    }
}
if (! function_exists('str_before')) {
    function str_before($subject, $search)
    {
        return Str::before($subject, $search);
    }
}
if (! function_exists('str_contains')) {
    function str_contains($haystack, $needles)
    {
        return Str::contains($haystack, $needles);
    }
}
if (! function_exists('str_finish')) {
    function str_finish($value, $cap)
    {
        return Str::finish($value, $cap);
    }
}
if (! function_exists('str_is')) {
    function str_is($pattern, $value)
    {
        return Str::is($pattern, $value);
    }
}
if (! function_exists('str_limit')) {
    function str_limit($value, $limit = 100, $end = '...')
    {
        return Str::limit($value, $limit, $end);
    }
}
if (! function_exists('str_plural')) {
    function str_plural($value, $count = 2)
    {
        return Str::plural($value, $count);
    }
}
if (! function_exists('str_random')) {
    function str_random($length = 16)
    {
        return Str::random($length);
    }
}
if (! function_exists('str_replace_array')) {
    function str_replace_array($search, array $replace, $subject)
    {
        return Str::replaceArray($search, $replace, $subject);
    }
}
if (! function_exists('str_replace_first')) {
    function str_replace_first($search, $replace, $subject)
    {
        return Str::replaceFirst($search, $replace, $subject);
    }
}
if (! function_exists('str_replace_last')) {
    function str_replace_last($search, $replace, $subject)
    {
        return Str::replaceLast($search, $replace, $subject);
    }
}
if (! function_exists('str_singular')) {
    function str_singular($value)
    {
        return Str::singular($value);
    }
}
if (! function_exists('str_slug')) {
    function str_slug($title, $separator = '-', $language = 'en')
    {
        return Str::slug($title, $separator, $language);
    }
}
if (! function_exists('str_start')) {
    function str_start($value, $prefix)
    {
        return Str::start($value, $prefix);
    }
}
if (! function_exists('studly_case')) {
    function studly_case($value)
    {
        return Str::studly($value);
    }
}
if (! function_exists('tap')) {
    function tap($value, $callback = null)
    {
        if (is_null($callback)) {
            return new HigherOrderTapProxy($value);
        }
        $callback($value);
        return $value;
    }
}
if (! function_exists('throw_if')) {
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }
        return $condition;
    }
}
if (! function_exists('throw_unless')) {
    function throw_unless($condition, $exception, ...$parameters)
    {
        if (! $condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }
        return $condition;
    }
}
if (! function_exists('title_case')) {
    function title_case($value)
    {
        return Str::title($value);
    }
}
if (! function_exists('trait_uses_recursive')) {
    function trait_uses_recursive($trait)
    {
        $traits = class_uses($trait);
        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }
        return $traits;
    }
}
if (! function_exists('transform')) {
    function transform($value, callable $callback, $default = null)
    {
        if (filled($value)) {
            return $callback($value);
        }
        if (is_callable($default)) {
            return $default($value);
        }
        return $default;
    }
}
if (! function_exists('value')) {
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}
if (! function_exists('windows_os')) {
    function windows_os()
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}
if (! function_exists('with')) {
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}
