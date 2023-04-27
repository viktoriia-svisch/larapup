<?php
namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class TaggedCache extends Repository
{
    use RetrievesMultipleKeys;
    protected $tags;
    public function __construct(Store $store, TagSet $tags)
    {
        parent::__construct($store);
        $this->tags = $tags;
    }
    public function increment($key, $value = 1)
    {
        $this->store->increment($this->itemKey($key), $value);
    }
    public function decrement($key, $value = 1)
    {
        $this->store->decrement($this->itemKey($key), $value);
    }
    public function flush()
    {
        $this->tags->reset();
        return true;
    }
    protected function itemKey($key)
    {
        return $this->taggedItemKey($key);
    }
    public function taggedItemKey($key)
    {
        return sha1($this->tags->getNamespace()).':'.$key;
    }
    protected function event($event)
    {
        parent::event($event->setTags($this->tags->getNames()));
    }
    public function getTags()
    {
        return $this->tags;
    }
}
