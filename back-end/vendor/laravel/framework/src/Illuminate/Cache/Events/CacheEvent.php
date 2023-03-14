<?php
namespace Illuminate\Cache\Events;
abstract class CacheEvent
{
    public $key;
    public $tags;
    public function __construct($key, array $tags = [])
    {
        $this->key = $key;
        $this->tags = $tags;
    }
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
    }
}
