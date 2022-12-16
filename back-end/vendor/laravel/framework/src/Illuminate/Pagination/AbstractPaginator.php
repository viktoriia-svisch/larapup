<?php
namespace Illuminate\Pagination;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Traits\ForwardsCalls;
abstract class AbstractPaginator implements Htmlable
{
    use ForwardsCalls;
    protected $items;
    protected $perPage;
    protected $currentPage;
    protected $path = '/';
    protected $query = [];
    protected $fragment;
    protected $pageName = 'page';
    public $onEachSide = 3;
    protected $options;
    protected static $currentPathResolver;
    protected static $currentPageResolver;
    protected static $viewFactoryResolver;
    public static $defaultView = 'pagination::bootstrap-4';
    public static $defaultSimpleView = 'pagination::simple-bootstrap-4';
    protected function isValidPageNumber($page)
    {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
    }
    public function previousPageUrl()
    {
        if ($this->currentPage() > 1) {
            return $this->url($this->currentPage() - 1);
        }
    }
    public function getUrlRange($start, $end)
    {
        return collect(range($start, $end))->mapWithKeys(function ($page) {
            return [$page => $this->url($page)];
        })->all();
    }
    public function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }
        $parameters = [$this->pageName => $page];
        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }
        return $this->path
                        .(Str::contains($this->path, '?') ? '&' : '?')
                        .Arr::query($parameters)
                        .$this->buildFragment();
    }
    public function fragment($fragment = null)
    {
        if (is_null($fragment)) {
            return $this->fragment;
        }
        $this->fragment = $fragment;
        return $this;
    }
    public function appends($key, $value = null)
    {
        if (is_null($key)) {
            return $this;
        }
        if (is_array($key)) {
            return $this->appendArray($key);
        }
        return $this->addQuery($key, $value);
    }
    protected function appendArray(array $keys)
    {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }
        return $this;
    }
    protected function addQuery($key, $value)
    {
        if ($key !== $this->pageName) {
            $this->query[$key] = $value;
        }
        return $this;
    }
    protected function buildFragment()
    {
        return $this->fragment ? '#'.$this->fragment : '';
    }
    public function loadMorph($relation, $relations)
    {
        $this->getCollection()->loadMorph($relation, $relations);
        return $this;
    }
    public function items()
    {
        return $this->items->all();
    }
    public function firstItem()
    {
        return count($this->items) > 0 ? ($this->currentPage - 1) * $this->perPage + 1 : null;
    }
    public function lastItem()
    {
        return count($this->items) > 0 ? $this->firstItem() + $this->count() - 1 : null;
    }
    public function perPage()
    {
        return $this->perPage;
    }
    public function hasPages()
    {
        return $this->currentPage() != 1 || $this->hasMorePages();
    }
    public function onFirstPage()
    {
        return $this->currentPage() <= 1;
    }
    public function currentPage()
    {
        return $this->currentPage;
    }
    public function getPageName()
    {
        return $this->pageName;
    }
    public function setPageName($name)
    {
        $this->pageName = $name;
        return $this;
    }
    public function withPath($path)
    {
        return $this->setPath($path);
    }
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }
    public function onEachSide($count)
    {
        $this->onEachSide = $count;
        return $this;
    }
    public static function resolveCurrentPath($default = '/')
    {
        if (isset(static::$currentPathResolver)) {
            return call_user_func(static::$currentPathResolver);
        }
        return $default;
    }
    public static function currentPathResolver(Closure $resolver)
    {
        static::$currentPathResolver = $resolver;
    }
    public static function resolveCurrentPage($pageName = 'page', $default = 1)
    {
        if (isset(static::$currentPageResolver)) {
            return call_user_func(static::$currentPageResolver, $pageName);
        }
        return $default;
    }
    public static function currentPageResolver(Closure $resolver)
    {
        static::$currentPageResolver = $resolver;
    }
    public static function viewFactory()
    {
        return call_user_func(static::$viewFactoryResolver);
    }
    public static function viewFactoryResolver(Closure $resolver)
    {
        static::$viewFactoryResolver = $resolver;
    }
    public static function defaultView($view)
    {
        static::$defaultView = $view;
    }
    public static function defaultSimpleView($view)
    {
        static::$defaultSimpleView = $view;
    }
    public static function useBootstrapThree()
    {
        static::defaultView('pagination::default');
        static::defaultSimpleView('pagination::simple-default');
    }
    public function getIterator()
    {
        return $this->items->getIterator();
    }
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }
    public function isNotEmpty()
    {
        return $this->items->isNotEmpty();
    }
    public function count()
    {
        return $this->items->count();
    }
    public function getCollection()
    {
        return $this->items;
    }
    public function setCollection(Collection $collection)
    {
        $this->items = $collection;
        return $this;
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function offsetExists($key)
    {
        return $this->items->has($key);
    }
    public function offsetGet($key)
    {
        return $this->items->get($key);
    }
    public function offsetSet($key, $value)
    {
        $this->items->put($key, $value);
    }
    public function offsetUnset($key)
    {
        $this->items->forget($key);
    }
    public function toHtml()
    {
        return (string) $this->render();
    }
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->getCollection(), $method, $parameters);
    }
    public function __toString()
    {
        return (string) $this->render();
    }
}
