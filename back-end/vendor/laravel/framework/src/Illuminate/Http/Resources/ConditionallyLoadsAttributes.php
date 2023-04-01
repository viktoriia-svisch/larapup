<?php
namespace Illuminate\Http\Resources;
use Illuminate\Support\Arr;
trait ConditionallyLoadsAttributes
{
    protected function filter($data)
    {
        $index = -1;
        $numericKeys = array_values($data) === $data;
        foreach ($data as $key => $value) {
            $index++;
            if (is_array($value)) {
                $data[$key] = $this->filter($value);
                continue;
            }
            if (is_numeric($key) && $value instanceof MergeValue) {
                return $this->mergeData($data, $index, $this->filter($value->data), $numericKeys);
            }
            if ($value instanceof self && is_null($value->resource)) {
                $data[$key] = null;
            }
        }
        return $this->removeMissingValues($data, $numericKeys);
    }
    protected function mergeData($data, $index, $merge, $numericKeys)
    {
        if ($numericKeys) {
            return $this->removeMissingValues(array_merge(
                array_merge(array_slice($data, 0, $index, true), $merge),
                $this->filter(array_values(array_slice($data, $index + 1, null, true)))
            ), $numericKeys);
        }
        return $this->removeMissingValues(array_slice($data, 0, $index, true) +
                $merge +
                $this->filter(array_slice($data, $index + 1, null, true)));
    }
    protected function removeMissingValues($data, $numericKeys = false)
    {
        foreach ($data as $key => $value) {
            if (($value instanceof PotentiallyMissing && $value->isMissing()) ||
                ($value instanceof self &&
                $value->resource instanceof PotentiallyMissing &&
                $value->isMissing())) {
                unset($data[$key]);
            }
        }
        return ! empty($data) && is_numeric(array_keys($data)[0])
                        ? array_values($data) : $data;
    }
    protected function when($condition, $value, $default = null)
    {
        if ($condition) {
            return value($value);
        }
        return func_num_args() === 3 ? value($default) : new MissingValue;
    }
    protected function merge($value)
    {
        return $this->mergeWhen(true, $value);
    }
    protected function mergeWhen($condition, $value)
    {
        return $condition ? new MergeValue(value($value)) : new MissingValue;
    }
    protected function attributes($attributes)
    {
        return new MergeValue(
            Arr::only($this->resource->toArray(), $attributes)
        );
    }
    protected function whenLoaded($relationship, $value = null, $default = null)
    {
        if (func_num_args() < 3) {
            $default = new MissingValue;
        }
        if (! $this->resource->relationLoaded($relationship)) {
            return value($default);
        }
        if (func_num_args() === 1) {
            return $this->resource->{$relationship};
        }
        if ($this->resource->{$relationship} === null) {
            return null;
        }
        return value($value);
    }
    protected function whenPivotLoaded($table, $value, $default = null)
    {
        return $this->whenPivotLoadedAs('pivot', ...func_get_args());
    }
    protected function whenPivotLoadedAs($accessor, $table, $value, $default = null)
    {
        if (func_num_args() === 3) {
            $default = new MissingValue;
        }
        return $this->when(
            $this->resource->$accessor &&
            ($this->resource->$accessor instanceof $table ||
            $this->resource->$accessor->getTable() === $table),
            ...[$value, $default]
        );
    }
    protected function transform($value, callable $callback, $default = null)
    {
        return transform(
            $value, $callback, func_num_args() === 3 ? $default : new MissingValue
        );
    }
}
