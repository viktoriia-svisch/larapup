<?php
namespace Illuminate\Database\Concerns;
use Illuminate\Container\Container;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
trait BuildsQueries
{
    public function chunk($count, callable $callback)
    {
        $this->enforceOrderBy();
        $page = 1;
        do {
            $results = $this->forPage($page, $count)->get();
            $countResults = $results->count();
            if ($countResults == 0) {
                break;
            }
            if ($callback($results, $page) === false) {
                return false;
            }
            unset($results);
            $page++;
        } while ($countResults == $count);
        return true;
    }
    public function each(callable $callback, $count = 1000)
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
        });
    }
    public function first($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }
    public function when($value, $callback, $default = null)
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        } elseif ($default) {
            return $default($this, $value) ?: $this;
        }
        return $this;
    }
    public function tap($callback)
    {
        return $this->when(true, $callback);
    }
    public function unless($value, $callback, $default = null)
    {
        if (! $value) {
            return $callback($this, $value) ?: $this;
        } elseif ($default) {
            return $default($this, $value) ?: $this;
        }
        return $this;
    }
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            'items', 'total', 'perPage', 'currentPage', 'options'
        ));
    }
    protected function simplePaginator($items, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(Paginator::class, compact(
            'items', 'perPage', 'currentPage', 'options'
        ));
    }
}
