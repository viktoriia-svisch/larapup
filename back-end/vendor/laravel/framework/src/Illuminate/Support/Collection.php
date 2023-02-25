<?php
namespace Illuminate\Support;
use stdClass;
use Countable;
use Exception;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use CachingIterator;
use JsonSerializable;
use IteratorAggregate;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Jsonable;
use Symfony\Component\VarDumper\VarDumper;
use Illuminate\Contracts\Support\Arrayable;
class Collection implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    use Macroable;
    protected $items = [];
    protected static $proxies = [
        'average', 'avg', 'contains', 'each', 'every', 'filter', 'first',
        'flatMap', 'groupBy', 'keyBy', 'map', 'max', 'min', 'partition',
        'reject', 'some', 'sortBy', 'sortByDesc', 'sum', 'unique',
    ];
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }
    public static function make($items = [])
    {
        return new static($items);
    }
    public static function wrap($value)
    {
        return $value instanceof self
            ? new static($value)
            : new static(Arr::wrap($value));
    }
    public static function unwrap($value)
    {
        return $value instanceof self ? $value->all() : $value;
    }
    public static function times($number, callable $callback = null)
    {
        if ($number < 1) {
            return new static;
        }
        if (is_null($callback)) {
            return new static(range(1, $number));
        }
        return (new static(range(1, $number)))->map($callback);
    }
    public function all()
    {
        return $this->items;
    }
    public function avg($callback = null)
    {
        $callback = $this->valueRetriever($callback);
        $items = $this->map(function ($value) use ($callback) {
            return $callback($value);
        })->filter(function ($value) {
            return ! is_null($value);
        });
        if ($count = $items->count()) {
            return $items->sum() / $count;
        }
    }
    public function average($callback = null)
    {
        return $this->avg($callback);
    }
    public function median($key = null)
    {
        $values = (isset($key) ? $this->pluck($key) : $this)
            ->filter(function ($item) {
                return ! is_null($item);
            })->sort()->values();
        $count = $values->count();
        if ($count == 0) {
            return;
        }
        $middle = (int) ($count / 2);
        if ($count % 2) {
            return $values->get($middle);
        }
        return (new static([
            $values->get($middle - 1), $values->get($middle),
        ]))->average();
    }
    public function mode($key = null)
    {
        if ($this->count() === 0) {
            return;
        }
        $collection = isset($key) ? $this->pluck($key) : $this;
        $counts = new self;
        $collection->each(function ($value) use ($counts) {
            $counts[$value] = isset($counts[$value]) ? $counts[$value] + 1 : 1;
        });
        $sorted = $counts->sort();
        $highestValue = $sorted->last();
        return $sorted->filter(function ($value) use ($highestValue) {
            return $value == $highestValue;
        })->sort()->keys()->all();
    }
    public function collapse()
    {
        return new static(Arr::collapse($this->items));
    }
    public function some($key, $operator = null, $value = null)
    {
        return $this->contains(...func_get_args());
    }
    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            if ($this->useAsCallable($key)) {
                $placeholder = new stdClass;
                return $this->first($key, $placeholder) !== $placeholder;
            }
            return in_array($key, $this->items);
        }
        return $this->contains($this->operatorForWhere(...func_get_args()));
    }
    public function containsStrict($key, $value = null)
    {
        if (func_num_args() === 2) {
            return $this->contains(function ($item) use ($key, $value) {
                return data_get($item, $key) === $value;
            });
        }
        if ($this->useAsCallable($key)) {
            return ! is_null($this->first($key));
        }
        return in_array($key, $this->items, true);
    }
    public function crossJoin(...$lists)
    {
        return new static(Arr::crossJoin(
            $this->items, ...array_map([$this, 'getArrayableItems'], $lists)
        ));
    }
    public function dd(...$args)
    {
        call_user_func_array([$this, 'dump'], $args);
        die(1);
    }
    public function dump()
    {
        (new static(func_get_args()))
            ->push($this)
            ->each(function ($item) {
                VarDumper::dump($item);
            });
        return $this;
    }
    public function diff($items)
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
    }
    public function diffUsing($items, callable $callback)
    {
        return new static(array_udiff($this->items, $this->getArrayableItems($items), $callback));
    }
    public function diffAssoc($items)
    {
        return new static(array_diff_assoc($this->items, $this->getArrayableItems($items)));
    }
    public function diffAssocUsing($items, callable $callback)
    {
        return new static(array_diff_uassoc($this->items, $this->getArrayableItems($items), $callback));
    }
    public function diffKeys($items)
    {
        return new static(array_diff_key($this->items, $this->getArrayableItems($items)));
    }
    public function diffKeysUsing($items, callable $callback)
    {
        return new static(array_diff_ukey($this->items, $this->getArrayableItems($items), $callback));
    }
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }
    public function eachSpread(callable $callback)
    {
        return $this->each(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;
            return $callback(...$chunk);
        });
    }
    public function every($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $callback = $this->valueRetriever($key);
            foreach ($this->items as $k => $v) {
                if (! $callback($v, $k)) {
                    return false;
                }
            }
            return true;
        }
        return $this->every($this->operatorForWhere(...func_get_args()));
    }
    public function except($keys)
    {
        if ($keys instanceof self) {
            $keys = $keys->all();
        } elseif (! is_array($keys)) {
            $keys = func_get_args();
        }
        return new static(Arr::except($this->items, $keys));
    }
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback));
        }
        return new static(array_filter($this->items));
    }
    public function when($value, callable $callback, callable $default = null)
    {
        if ($value) {
            return $callback($this, $value);
        } elseif ($default) {
            return $default($this, $value);
        }
        return $this;
    }
    public function whenEmpty(callable $callback, callable $default = null)
    {
        return $this->when($this->isEmpty(), $callback, $default);
    }
    public function whenNotEmpty(callable $callback, callable $default = null)
    {
        return $this->when($this->isNotEmpty(), $callback, $default);
    }
    public function unless($value, callable $callback, callable $default = null)
    {
        return $this->when(! $value, $callback, $default);
    }
    public function unlessEmpty(callable $callback, callable $default = null)
    {
        return $this->whenNotEmpty($callback, $default);
    }
    public function unlessNotEmpty(callable $callback, callable $default = null)
    {
        return $this->whenEmpty($callback, $default);
    }
    public function where($key, $operator = null, $value = null)
    {
        return $this->filter($this->operatorForWhere(...func_get_args()));
    }
    protected function operatorForWhere($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $value = true;
            $operator = '=';
        }
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);
            $strings = array_filter([$retrieved, $value], function ($value) {
                return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
            });
            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }
            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;
                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
            }
        };
    }
    public function whereStrict($key, $value)
    {
        return $this->where($key, '===', $value);
    }
    public function whereIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);
        return $this->filter(function ($item) use ($key, $values, $strict) {
            return in_array(data_get($item, $key), $values, $strict);
        });
    }
    public function whereInStrict($key, $values)
    {
        return $this->whereIn($key, $values, true);
    }
    public function whereBetween($key, $values)
    {
        return $this->where($key, '>=', reset($values))->where($key, '<=', end($values));
    }
    public function whereNotBetween($key, $values)
    {
        return $this->filter(function ($item) use ($key, $values) {
            return data_get($item, $key) < reset($values) || data_get($item, $key) > end($values);
        });
    }
    public function whereNotIn($key, $values, $strict = false)
    {
        $values = $this->getArrayableItems($values);
        return $this->reject(function ($item) use ($key, $values, $strict) {
            return in_array(data_get($item, $key), $values, $strict);
        });
    }
    public function whereNotInStrict($key, $values)
    {
        return $this->whereNotIn($key, $values, true);
    }
    public function whereInstanceOf($type)
    {
        return $this->filter(function ($value) use ($type) {
            return $value instanceof $type;
        });
    }
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }
    public function firstWhere($key, $operator, $value = null)
    {
        return $this->first($this->operatorForWhere(...func_get_args()));
    }
    public function flatten($depth = INF)
    {
        return new static(Arr::flatten($this->items, $depth));
    }
    public function flip()
    {
        return new static(array_flip($this->items));
    }
    public function forget($keys)
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }
        return $this;
    }
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }
        return value($default);
    }
    public function groupBy($groupBy, $preserveKeys = false)
    {
        if (is_array($groupBy)) {
            $nextGroups = $groupBy;
            $groupBy = array_shift($nextGroups);
        }
        $groupBy = $this->valueRetriever($groupBy);
        $results = [];
        foreach ($this->items as $key => $value) {
            $groupKeys = $groupBy($value, $key);
            if (! is_array($groupKeys)) {
                $groupKeys = [$groupKeys];
            }
            foreach ($groupKeys as $groupKey) {
                $groupKey = is_bool($groupKey) ? (int) $groupKey : $groupKey;
                if (! array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = new static;
                }
                $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
            }
        }
        $result = new static($results);
        if (! empty($nextGroups)) {
            return $result->map->groupBy($nextGroups, $preserveKeys);
        }
        return $result;
    }
    public function keyBy($keyBy)
    {
        $keyBy = $this->valueRetriever($keyBy);
        $results = [];
        foreach ($this->items as $key => $item) {
            $resolvedKey = $keyBy($item, $key);
            if (is_object($resolvedKey)) {
                $resolvedKey = (string) $resolvedKey;
            }
            $results[$resolvedKey] = $item;
        }
        return new static($results);
    }
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();
        foreach ($keys as $value) {
            if (! $this->offsetExists($value)) {
                return false;
            }
        }
        return true;
    }
    public function implode($value, $glue = null)
    {
        $first = $this->first();
        if (is_array($first) || is_object($first)) {
            return implode($glue, $this->pluck($value)->all());
        }
        return implode($value, $this->items);
    }
    public function intersect($items)
    {
        return new static(array_intersect($this->items, $this->getArrayableItems($items)));
    }
    public function intersectByKeys($items)
    {
        return new static(array_intersect_key(
            $this->items, $this->getArrayableItems($items)
        ));
    }
    public function isEmpty()
    {
        return empty($this->items);
    }
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }
    public function keys()
    {
        return new static(array_keys($this->items));
    }
    public function last(callable $callback = null, $default = null)
    {
        return Arr::last($this->items, $callback, $default);
    }
    public function pluck($value, $key = null)
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);
        return new static(array_combine($keys, $items));
    }
    public function mapSpread(callable $callback)
    {
        return $this->map(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;
            return $callback(...$chunk);
        });
    }
    public function mapToDictionary(callable $callback)
    {
        $dictionary = [];
        foreach ($this->items as $key => $item) {
            $pair = $callback($item, $key);
            $key = key($pair);
            $value = reset($pair);
            if (! isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }
            $dictionary[$key][] = $value;
        }
        return new static($dictionary);
    }
    public function mapToGroups(callable $callback)
    {
        $groups = $this->mapToDictionary($callback);
        return $groups->map([$this, 'make']);
    }
    public function mapWithKeys(callable $callback)
    {
        $result = [];
        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);
            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }
        return new static($result);
    }
    public function flatMap(callable $callback)
    {
        return $this->map($callback)->collapse();
    }
    public function mapInto($class)
    {
        return $this->map(function ($value, $key) use ($class) {
            return new $class($value, $key);
        });
    }
    public function max($callback = null)
    {
        $callback = $this->valueRetriever($callback);
        return $this->filter(function ($value) {
            return ! is_null($value);
        })->reduce(function ($result, $item) use ($callback) {
            $value = $callback($item);
            return is_null($result) || $value > $result ? $value : $result;
        });
    }
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }
    public function combine($values)
    {
        return new static(array_combine($this->all(), $this->getArrayableItems($values)));
    }
    public function union($items)
    {
        return new static($this->items + $this->getArrayableItems($items));
    }
    public function min($callback = null)
    {
        $callback = $this->valueRetriever($callback);
        return $this->map(function ($value) use ($callback) {
            return $callback($value);
        })->filter(function ($value) {
            return ! is_null($value);
        })->reduce(function ($result, $value) {
            return is_null($result) || $value < $result ? $value : $result;
        });
    }
    public function nth($step, $offset = 0)
    {
        $new = [];
        $position = 0;
        foreach ($this->items as $item) {
            if ($position % $step === $offset) {
                $new[] = $item;
            }
            $position++;
        }
        return new static($new);
    }
    public function only($keys)
    {
        if (is_null($keys)) {
            return new static($this->items);
        }
        if ($keys instanceof self) {
            $keys = $keys->all();
        }
        $keys = is_array($keys) ? $keys : func_get_args();
        return new static(Arr::only($this->items, $keys));
    }
    public function forPage($page, $perPage)
    {
        $offset = max(0, ($page - 1) * $perPage);
        return $this->slice($offset, $perPage);
    }
    public function partition($key, $operator = null, $value = null)
    {
        $partitions = [new static, new static];
        $callback = func_num_args() === 1
                ? $this->valueRetriever($key)
                : $this->operatorForWhere(...func_get_args());
        foreach ($this->items as $key => $item) {
            $partitions[(int) ! $callback($item, $key)][$key] = $item;
        }
        return new static($partitions);
    }
    public function pipe(callable $callback)
    {
        return $callback($this);
    }
    public function pop()
    {
        return array_pop($this->items);
    }
    public function prepend($value, $key = null)
    {
        $this->items = Arr::prepend($this->items, $value, $key);
        return $this;
    }
    public function push($value)
    {
        $this->offsetSet(null, $value);
        return $this;
    }
    public function concat($source)
    {
        $result = new static($this);
        foreach ($source as $item) {
            $result->push($item);
        }
        return $result;
    }
    public function pull($key, $default = null)
    {
        return Arr::pull($this->items, $key, $default);
    }
    public function put($key, $value)
    {
        $this->offsetSet($key, $value);
        return $this;
    }
    public function random($number = null)
    {
        if (is_null($number)) {
            return Arr::random($this->items);
        }
        return new static(Arr::random($this->items, $number));
    }
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }
    public function reject($callback)
    {
        if ($this->useAsCallable($callback)) {
            return $this->filter(function ($value, $key) use ($callback) {
                return ! $callback($value, $key);
            });
        }
        return $this->filter(function ($item) use ($callback) {
            return $item != $callback;
        });
    }
    public function reverse()
    {
        return new static(array_reverse($this->items, true));
    }
    public function search($value, $strict = false)
    {
        if (! $this->useAsCallable($value)) {
            return array_search($value, $this->items, $strict);
        }
        foreach ($this->items as $key => $item) {
            if (call_user_func($value, $item, $key)) {
                return $key;
            }
        }
        return false;
    }
    public function shift()
    {
        return array_shift($this->items);
    }
    public function shuffle($seed = null)
    {
        return new static(Arr::shuffle($this->items, $seed));
    }
    public function slice($offset, $length = null)
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }
    public function split($numberOfGroups)
    {
        if ($this->isEmpty()) {
            return new static;
        }
        $groups = new static;
        $groupSize = floor($this->count() / $numberOfGroups);
        $remain = $this->count() % $numberOfGroups;
        $start = 0;
        for ($i = 0; $i < $numberOfGroups; $i++) {
            $size = $groupSize;
            if ($i < $remain) {
                $size++;
            }
            if ($size) {
                $groups->push(new static(array_slice($this->items, $start, $size)));
                $start += $size;
            }
        }
        return $groups;
    }
    public function chunk($size)
    {
        if ($size <= 0) {
            return new static;
        }
        $chunks = [];
        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }
        return new static($chunks);
    }
    public function sort(callable $callback = null)
    {
        $items = $this->items;
        $callback
            ? uasort($items, $callback)
            : asort($items);
        return new static($items);
    }
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];
        $callback = $this->valueRetriever($callback);
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }
        $descending ? arsort($results, $options)
            : asort($results, $options);
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }
        return new static($results);
    }
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }
    public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        $items = $this->items;
        $descending ? krsort($items, $options) : ksort($items, $options);
        return new static($items);
    }
    public function sortKeysDesc($options = SORT_REGULAR)
    {
        return $this->sortKeys($options, true);
    }
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() === 1) {
            return new static(array_splice($this->items, $offset));
        }
        return new static(array_splice($this->items, $offset, $length, $replacement));
    }
    public function sum($callback = null)
    {
        if (is_null($callback)) {
            return array_sum($this->items);
        }
        $callback = $this->valueRetriever($callback);
        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }
        return $this->slice(0, $limit);
    }
    public function tap(callable $callback)
    {
        $callback(new static($this->items));
        return $this;
    }
    public function transform(callable $callback)
    {
        $this->items = $this->map($callback)->all();
        return $this;
    }
    public function unique($key = null, $strict = false)
    {
        $callback = $this->valueRetriever($key);
        $exists = [];
        return $this->reject(function ($item, $key) use ($callback, $strict, &$exists) {
            if (in_array($id = $callback($item, $key), $exists, $strict)) {
                return true;
            }
            $exists[] = $id;
        });
    }
    public function uniqueStrict($key = null)
    {
        return $this->unique($key, true);
    }
    public function values()
    {
        return new static(array_values($this->items));
    }
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }
        return function ($item) use ($value) {
            return data_get($item, $value);
        };
    }
    public function zip($items)
    {
        $arrayableItems = array_map(function ($items) {
            return $this->getArrayableItems($items);
        }, func_get_args());
        $params = array_merge([function () {
            return new static(func_get_args());
        }, $this->items], $arrayableItems);
        return new static(call_user_func_array('array_map', $params));
    }
    public function pad($size, $value)
    {
        return new static(array_pad($this->items, $size, $value));
    }
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }
            return $value;
        }, $this->items);
    }
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
    {
        return new CachingIterator($this->getIterator(), $flags);
    }
    public function count()
    {
        return count($this->items);
    }
    public function toBase()
    {
        return new self($this);
    }
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }
    public function offsetGet($key)
    {
        return $this->items[$key];
    }
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }
    public function __toString()
    {
        return $this->toJson();
    }
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }
        return (array) $items;
    }
    public static function proxy($method)
    {
        static::$proxies[] = $method;
    }
    public function __get($key)
    {
        if (! in_array($key, static::$proxies)) {
            throw new Exception("Property [{$key}] does not exist on this collection instance.");
        }
        return new HigherOrderCollectionProxy($this, $key);
    }
}
