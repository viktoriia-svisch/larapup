<?php
namespace Illuminate\Http\Resources;
use Illuminate\Support\Str;
use Illuminate\Pagination\AbstractPaginator;
trait CollectsResources
{
    protected function collectResource($resource)
    {
        if ($resource instanceof MissingValue) {
            return $resource;
        }
        $collects = $this->collects();
        $this->collection = $collects && ! $resource->first() instanceof $collects
            ? $resource->mapInto($collects)
            : $resource->toBase();
        return $resource instanceof AbstractPaginator
                    ? $resource->setCollection($this->collection)
                    : $this->collection;
    }
    protected function collects()
    {
        if ($this->collects) {
            return $this->collects;
        }
        if (Str::endsWith(class_basename($this), 'Collection') &&
            class_exists($class = Str::replaceLast('Collection', '', get_class($this)))) {
            return $class;
        }
    }
    public function getIterator()
    {
        return $this->collection->getIterator();
    }
}
