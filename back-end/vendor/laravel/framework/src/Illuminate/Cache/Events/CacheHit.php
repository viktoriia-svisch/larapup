<?php
namespace Illuminate\Cache\Events;
class CacheHit extends CacheEvent
{
    public $value;
    public function __construct($key, $value, array $tags = [])
    {
        parent::__construct($key, $tags);
        $this->value = $value;
    }
}
