<?php
namespace Illuminate\Routing;
use Illuminate\Support\Collection;
class SortedMiddleware extends Collection
{
    public function __construct(array $priorityMap, $middlewares)
    {
        if ($middlewares instanceof Collection) {
            $middlewares = $middlewares->all();
        }
        $this->items = $this->sortMiddleware($priorityMap, $middlewares);
    }
    protected function sortMiddleware($priorityMap, $middlewares)
    {
        $lastIndex = 0;
        foreach ($middlewares as $index => $middleware) {
            if (! is_string($middleware)) {
                continue;
            }
            $stripped = head(explode(':', $middleware));
            if (in_array($stripped, $priorityMap)) {
                $priorityIndex = array_search($stripped, $priorityMap);
                if (isset($lastPriorityIndex) && $priorityIndex < $lastPriorityIndex) {
                    return $this->sortMiddleware(
                        $priorityMap, array_values($this->moveMiddleware($middlewares, $index, $lastIndex))
                    );
                }
                $lastIndex = $index;
                $lastPriorityIndex = $priorityIndex;
            }
        }
        return array_values(array_unique($middlewares, SORT_REGULAR));
    }
    protected function moveMiddleware($middlewares, $from, $to)
    {
        array_splice($middlewares, $to, 0, $middlewares[$from]);
        unset($middlewares[$from + 1]);
        return $middlewares;
    }
}
