<?php
namespace Illuminate\Cache;
use Illuminate\Contracts\Cache\Store;
class TagSet
{
    protected $store;
    protected $names = [];
    public function __construct(Store $store, array $names = [])
    {
        $this->store = $store;
        $this->names = $names;
    }
    public function reset()
    {
        array_walk($this->names, [$this, 'resetTag']);
    }
    public function resetTag($name)
    {
        $this->store->forever($this->tagKey($name), $id = str_replace('.', '', uniqid('', true)));
        return $id;
    }
    public function getNamespace()
    {
        return implode('|', $this->tagIds());
    }
    protected function tagIds()
    {
        return array_map([$this, 'tagId'], $this->names);
    }
    public function tagId($name)
    {
        return $this->store->get($this->tagKey($name)) ?: $this->resetTag($name);
    }
    public function tagKey($name)
    {
        return 'tag:'.$name.':key';
    }
    public function getNames()
    {
        return $this->names;
    }
}
