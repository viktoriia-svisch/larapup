<?php
namespace Illuminate\Database\Eloquent\Concerns;
use Illuminate\Support\Carbon;
trait HasTimestamps
{
    public $timestamps = true;
    public function touch()
    {
        if (! $this->usesTimestamps()) {
            return false;
        }
        $this->updateTimestamps();
        return $this->save();
    }
    protected function updateTimestamps()
    {
        $time = $this->freshTimestamp();
        if (! is_null(static::UPDATED_AT) && ! $this->isDirty(static::UPDATED_AT)) {
            $this->setUpdatedAt($time);
        }
        if (! $this->exists && ! is_null(static::CREATED_AT) &&
            ! $this->isDirty(static::CREATED_AT)) {
            $this->setCreatedAt($time);
        }
    }
    public function setCreatedAt($value)
    {
        $this->{static::CREATED_AT} = $value;
        return $this;
    }
    public function setUpdatedAt($value)
    {
        $this->{static::UPDATED_AT} = $value;
        return $this;
    }
    public function freshTimestamp()
    {
        return new Carbon;
    }
    public function freshTimestampString()
    {
        return $this->fromDateTime($this->freshTimestamp());
    }
    public function usesTimestamps()
    {
        return $this->timestamps;
    }
    public function getCreatedAtColumn()
    {
        return static::CREATED_AT;
    }
    public function getUpdatedAtColumn()
    {
        return static::UPDATED_AT;
    }
}
