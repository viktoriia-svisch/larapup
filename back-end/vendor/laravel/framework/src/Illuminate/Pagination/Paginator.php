<?php
namespace Illuminate\Pagination;
use Countable;
use ArrayAccess;
use JsonSerializable;
use IteratorAggregate;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
class Paginator extends AbstractPaginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable, PaginatorContract
{
    protected $hasMore;
    public function __construct($items, $perPage, $currentPage = null, array $options = [])
    {
        $this->options = $options;
        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
        $this->perPage = $perPage;
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;
        $this->setItems($items);
    }
    protected function setCurrentPage($currentPage)
    {
        $currentPage = $currentPage ?: static::resolveCurrentPage();
        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);
        $this->hasMore = $this->items->count() > $this->perPage;
        $this->items = $this->items->slice(0, $this->perPage);
    }
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
    }
    public function links($view = null, $data = [])
    {
        return $this->render($view, $data);
    }
    public function render($view = null, $data = [])
    {
        return new HtmlString(
            static::viewFactory()->make($view ?: static::$defaultSimpleView, array_merge($data, [
                'paginator' => $this,
            ]))->render()
        );
    }
    public function hasMorePagesWhen($hasMore = true)
    {
        $this->hasMore = $hasMore;
        return $this;
    }
    public function hasMorePages()
    {
        return $this->hasMore;
    }
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
        ];
    }
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
