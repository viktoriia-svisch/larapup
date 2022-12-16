<?php
namespace Illuminate\Database\Eloquent;
trait SoftDeletes
{
    protected $forceDeleting = false;
    public static function bootSoftDeletes()
    {
        static::addGlobalScope(new SoftDeletingScope);
    }
    public function forceDelete()
    {
        $this->forceDeleting = true;
        return tap($this->delete(), function ($deleted) {
            $this->forceDeleting = false;
            if ($deleted) {
                $this->fireModelEvent('forceDeleted', false);
            }
        });
    }
    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting) {
            $this->exists = false;
            return $this->newModelQuery()->where($this->getKeyName(), $this->getKey())->forceDelete();
        }
        return $this->runSoftDelete();
    }
    protected function runSoftDelete()
    {
        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());
        $time = $this->freshTimestamp();
        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];
        $this->{$this->getDeletedAtColumn()} = $time;
        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;
            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }
        $query->update($columns);
    }
    public function restore()
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }
        $this->{$this->getDeletedAtColumn()} = null;
        $this->exists = true;
        $result = $this->save();
        $this->fireModelEvent('restored', false);
        return $result;
    }
    public function trashed()
    {
        return ! is_null($this->{$this->getDeletedAtColumn()});
    }
    public static function restoring($callback)
    {
        static::registerModelEvent('restoring', $callback);
    }
    public static function restored($callback)
    {
        static::registerModelEvent('restored', $callback);
    }
    public function isForceDeleting()
    {
        return $this->forceDeleting;
    }
    public function getDeletedAtColumn()
    {
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }
    public function getQualifiedDeletedAtColumn()
    {
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }
}
