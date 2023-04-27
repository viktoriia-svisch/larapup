<?php
namespace Illuminate\Database\Eloquent\Concerns;
use Illuminate\Support\Str;
trait GuardsAttributes
{
    protected $fillable = [];
    protected $guarded = ['*'];
    protected static $unguarded = false;
    public function getFillable()
    {
        return $this->fillable;
    }
    public function fillable(array $fillable)
    {
        $this->fillable = $fillable;
        return $this;
    }
    public function getGuarded()
    {
        return $this->guarded;
    }
    public function guard(array $guarded)
    {
        $this->guarded = $guarded;
        return $this;
    }
    public static function unguard($state = true)
    {
        static::$unguarded = $state;
    }
    public static function reguard()
    {
        static::$unguarded = false;
    }
    public static function isUnguarded()
    {
        return static::$unguarded;
    }
    public static function unguarded(callable $callback)
    {
        if (static::$unguarded) {
            return $callback();
        }
        static::unguard();
        try {
            return $callback();
        } finally {
            static::reguard();
        }
    }
    public function isFillable($key)
    {
        if (static::$unguarded) {
            return true;
        }
        if (in_array($key, $this->getFillable())) {
            return true;
        }
        if ($this->isGuarded($key)) {
            return false;
        }
        return empty($this->getFillable()) &&
            ! Str::startsWith($key, '_');
    }
    public function isGuarded($key)
    {
        return in_array($key, $this->getGuarded()) || $this->getGuarded() == ['*'];
    }
    public function totallyGuarded()
    {
        return count($this->getFillable()) === 0 && $this->getGuarded() == ['*'];
    }
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0 && ! static::$unguarded) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }
        return $attributes;
    }
}
