<?php
namespace Illuminate\Cache\Events;
class KeyWritten extends CacheEvent
{
    public $value;
    public $minutes;
    public function __construct($key, $value, $minutes, $tags = [])
    {
        parent::__construct($key, $tags);
        $this->value = $value;
        $this->minutes = $minutes;
    }
}
