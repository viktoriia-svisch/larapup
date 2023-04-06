<?php
namespace Illuminate\Database\Eloquent\Relations\Concerns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
trait InteractsWithPivotTable
{
    public function toggle($ids, $touch = true)
    {
        $changes = [
            'attached' => [], 'detached' => [],
        ];
        $records = $this->formatRecordsList($this->parseIds($ids));
        $detach = array_values(array_intersect(
            $this->newPivotQuery()->pluck($this->relatedPivotKey)->all(),
            array_keys($records)
        ));
        if (count($detach) > 0) {
            $this->detach($detach, false);
            $changes['detached'] = $this->castKeys($detach);
        }
        $attach = array_diff_key($records, array_flip($detach));
        if (count($attach) > 0) {
            $this->attach($attach, [], false);
            $changes['attached'] = array_keys($attach);
        }
        if ($touch && (count($changes['attached']) ||
                       count($changes['detached']))) {
            $this->touchIfTouching();
        }
        return $changes;
    }
    public function syncWithoutDetaching($ids)
    {
        return $this->sync($ids, false);
    }
    public function sync($ids, $detaching = true)
    {
        $changes = [
            'attached' => [], 'detached' => [], 'updated' => [],
        ];
        $current = $this->newPivotQuery()->pluck(
            $this->relatedPivotKey
        )->all();
        $detach = array_diff($current, array_keys(
            $records = $this->formatRecordsList($this->parseIds($ids))
        ));
        if ($detaching && count($detach) > 0) {
            $this->detach($detach);
            $changes['detached'] = $this->castKeys($detach);
        }
        $changes = array_merge(
            $changes, $this->attachNew($records, $current, false)
        );
        if (count($changes['attached']) ||
            count($changes['updated'])) {
            $this->touchIfTouching();
        }
        return $changes;
    }
    protected function formatRecordsList(array $records)
    {
        return collect($records)->mapWithKeys(function ($attributes, $id) {
            if (! is_array($attributes)) {
                [$id, $attributes] = [$attributes, []];
            }
            return [$id => $attributes];
        })->all();
    }
    protected function attachNew(array $records, array $current, $touch = true)
    {
        $changes = ['attached' => [], 'updated' => []];
        foreach ($records as $id => $attributes) {
            if (! in_array($id, $current)) {
                $this->attach($id, $attributes, $touch);
                $changes['attached'][] = $this->castKey($id);
            }
            elseif (count($attributes) > 0 &&
                $this->updateExistingPivot($id, $attributes, $touch)) {
                $changes['updated'][] = $this->castKey($id);
            }
        }
        return $changes;
    }
    public function updateExistingPivot($id, array $attributes, $touch = true)
    {
        if (in_array($this->updatedAt(), $this->pivotColumns)) {
            $attributes = $this->addTimestampsToAttachment($attributes, true);
        }
        $updated = $this->newPivotStatementForId($this->parseId($id))->update(
            $this->castAttributes($attributes)
        );
        if ($touch) {
            $this->touchIfTouching();
        }
        return $updated;
    }
    public function attach($id, array $attributes = [], $touch = true)
    {
        $this->newPivotStatement()->insert($this->formatAttachRecords(
            $this->parseIds($id), $attributes
        ));
        if ($touch) {
            $this->touchIfTouching();
        }
    }
    protected function formatAttachRecords($ids, array $attributes)
    {
        $records = [];
        $hasTimestamps = ($this->hasPivotColumn($this->createdAt()) ||
                  $this->hasPivotColumn($this->updatedAt()));
        foreach ($ids as $key => $value) {
            $records[] = $this->formatAttachRecord(
                $key, $value, $attributes, $hasTimestamps
            );
        }
        return $records;
    }
    protected function formatAttachRecord($key, $value, $attributes, $hasTimestamps)
    {
        [$id, $attributes] = $this->extractAttachIdAndAttributes($key, $value, $attributes);
        return array_merge(
            $this->baseAttachRecord($id, $hasTimestamps), $this->castAttributes($attributes)
        );
    }
    protected function extractAttachIdAndAttributes($key, $value, array $attributes)
    {
        return is_array($value)
                    ? [$key, array_merge($value, $attributes)]
                    : [$value, $attributes];
    }
    protected function baseAttachRecord($id, $timed)
    {
        $record[$this->relatedPivotKey] = $id;
        $record[$this->foreignPivotKey] = $this->parent->{$this->parentKey};
        if ($timed) {
            $record = $this->addTimestampsToAttachment($record);
        }
        foreach ($this->pivotValues as $value) {
            $record[$value['column']] = $value['value'];
        }
        return $record;
    }
    protected function addTimestampsToAttachment(array $record, $exists = false)
    {
        $fresh = $this->parent->freshTimestamp();
        if ($this->using) {
            $pivotModel = new $this->using;
            $fresh = $fresh->format($pivotModel->getDateFormat());
        }
        if (! $exists && $this->hasPivotColumn($this->createdAt())) {
            $record[$this->createdAt()] = $fresh;
        }
        if ($this->hasPivotColumn($this->updatedAt())) {
            $record[$this->updatedAt()] = $fresh;
        }
        return $record;
    }
    protected function hasPivotColumn($column)
    {
        return in_array($column, $this->pivotColumns);
    }
    public function detach($ids = null, $touch = true)
    {
        $query = $this->newPivotQuery();
        if (! is_null($ids)) {
            $ids = $this->parseIds($ids);
            if (empty($ids)) {
                return 0;
            }
            $query->whereIn($this->relatedPivotKey, (array) $ids);
        }
        $results = $query->delete();
        if ($touch) {
            $this->touchIfTouching();
        }
        return $results;
    }
    public function newPivot(array $attributes = [], $exists = false)
    {
        $pivot = $this->related->newPivot(
            $this->parent, $attributes, $this->table, $exists, $this->using
        );
        return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
    }
    public function newExistingPivot(array $attributes = [])
    {
        return $this->newPivot($attributes, true);
    }
    public function newPivotStatement()
    {
        return $this->query->getQuery()->newQuery()->from($this->table);
    }
    public function newPivotStatementForId($id)
    {
        return $this->newPivotQuery()->whereIn($this->relatedPivotKey, $this->parseIds($id));
    }
    protected function newPivotQuery()
    {
        $query = $this->newPivotStatement();
        foreach ($this->pivotWheres as $arguments) {
            call_user_func_array([$query, 'where'], $arguments);
        }
        foreach ($this->pivotWhereIns as $arguments) {
            call_user_func_array([$query, 'whereIn'], $arguments);
        }
        return $query->where($this->foreignPivotKey, $this->parent->{$this->parentKey});
    }
    public function withPivot($columns)
    {
        $this->pivotColumns = array_merge(
            $this->pivotColumns, is_array($columns) ? $columns : func_get_args()
        );
        return $this;
    }
    protected function parseIds($value)
    {
        if ($value instanceof Model) {
            return [$value->{$this->relatedKey}];
        }
        if ($value instanceof Collection) {
            return $value->pluck($this->relatedKey)->all();
        }
        if ($value instanceof BaseCollection) {
            return $value->toArray();
        }
        return (array) $value;
    }
    protected function parseId($value)
    {
        return $value instanceof Model ? $value->{$this->relatedKey} : $value;
    }
    protected function castKeys(array $keys)
    {
        return array_map(function ($v) {
            return $this->castKey($v);
        }, $keys);
    }
    protected function castKey($key)
    {
        return $this->getTypeSwapValue(
            $this->related->getKeyType(),
            $key
        );
    }
    protected function castAttributes($attributes)
    {
        return $this->using
                    ? $this->newPivot()->fill($attributes)->getAttributes()
                    : $attributes;
    }
    protected function getTypeSwapValue($type, $value)
    {
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            default:
                return $value;
        }
    }
}
