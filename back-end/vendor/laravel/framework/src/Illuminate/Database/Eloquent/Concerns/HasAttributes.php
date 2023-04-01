<?php
namespace Illuminate\Database\Eloquent\Concerns;
use LogicException;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Database\Eloquent\JsonEncodingException;
trait HasAttributes
{
    protected $attributes = [];
    protected $original = [];
    protected $changes = [];
    protected $casts = [];
    protected $dates = [];
    protected $dateFormat;
    protected $appends = [];
    public static $snakeAttributes = true;
    protected static $mutatorCache = [];
    public function attributesToArray()
    {
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableAttributes()
        );
        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );
        foreach ($this->getArrayableAppends() as $key) {
            $attributes[$key] = $this->mutateAttributeForArray($key, null);
        }
        return $attributes;
    }
    protected function addDateAttributesToArray(array $attributes)
    {
        foreach ($this->getDates() as $key) {
            if (! isset($attributes[$key])) {
                continue;
            }
            $attributes[$key] = $this->serializeDate(
                $this->asDateTime($attributes[$key])
            );
        }
        return $attributes;
    }
    protected function addMutatedAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        foreach ($mutatedAttributes as $key) {
            if (! array_key_exists($key, $attributes)) {
                continue;
            }
            $attributes[$key] = $this->mutateAttributeForArray(
                $key, $attributes[$key]
            );
        }
        return $attributes;
    }
    protected function addCastAttributesToArray(array $attributes, array $mutatedAttributes)
    {
        foreach ($this->getCasts() as $key => $value) {
            if (! array_key_exists($key, $attributes) || in_array($key, $mutatedAttributes)) {
                continue;
            }
            $attributes[$key] = $this->castAttribute(
                $key, $attributes[$key]
            );
            if ($attributes[$key] &&
                ($value === 'date' || $value === 'datetime')) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }
            if ($attributes[$key] && $this->isCustomDateTimeCast($value)) {
                $attributes[$key] = $attributes[$key]->format(explode(':', $value, 2)[1]);
            }
        }
        return $attributes;
    }
    protected function getArrayableAttributes()
    {
        return $this->getArrayableItems($this->attributes);
    }
    protected function getArrayableAppends()
    {
        if (! count($this->appends)) {
            return [];
        }
        return $this->getArrayableItems(
            array_combine($this->appends, $this->appends)
        );
    }
    public function relationsToArray()
    {
        $attributes = [];
        foreach ($this->getArrayableRelations() as $key => $value) {
            if ($value instanceof Arrayable) {
                $relation = $value->toArray();
            }
            elseif (is_null($value)) {
                $relation = $value;
            }
            if (static::$snakeAttributes) {
                $key = Str::snake($key);
            }
            if (isset($relation) || is_null($value)) {
                $attributes[$key] = $relation;
            }
            unset($relation);
        }
        return $attributes;
    }
    protected function getArrayableRelations()
    {
        return $this->getArrayableItems($this->relations);
    }
    protected function getArrayableItems(array $values)
    {
        if (count($this->getVisible()) > 0) {
            $values = array_intersect_key($values, array_flip($this->getVisible()));
        }
        if (count($this->getHidden()) > 0) {
            $values = array_diff_key($values, array_flip($this->getHidden()));
        }
        return $values;
    }
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }
        if (array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }
        if (method_exists(self::class, $key)) {
            return;
        }
        return $this->getRelationValue($key);
    }
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }
        if (in_array($key, $this->getDates()) &&
            ! is_null($value)) {
            return $this->asDateTime($value);
        }
        return $value;
    }
    protected function getAttributeFromArray($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }
    public function getRelationValue($key)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }
    protected function getRelationshipFromMethod($method)
    {
        $relation = $this->$method();
        if (! $relation instanceof Relation) {
            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.', static::class, $method
            ));
        }
        return tap($relation->getResults(), function ($results) use ($method) {
            $this->setRelation($method, $results);
        });
    }
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get'.Str::studly($key).'Attribute'}($value);
    }
    protected function mutateAttributeForArray($key, $value)
    {
        $value = $this->mutateAttribute($key, $value);
        return $value instanceof Arrayable ? $value->toArray() : $value;
    }
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }
        switch ($this->getCastType($key)) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return $this->fromFloat($value);
            case 'decimal':
                return $this->asDecimal($value, explode(':', $this->getCasts()[$key], 2)[1]);
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return $this->fromJson($value, true);
            case 'array':
            case 'json':
                return $this->fromJson($value);
            case 'collection':
                return new BaseCollection($this->fromJson($value));
            case 'date':
                return $this->asDate($value);
            case 'datetime':
            case 'custom_datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimestamp($value);
            default:
                return $value;
        }
    }
    protected function getCastType($key)
    {
        if ($this->isCustomDateTimeCast($this->getCasts()[$key])) {
            return 'custom_datetime';
        }
        if ($this->isDecimalCast($this->getCasts()[$key])) {
            return 'decimal';
        }
        return trim(strtolower($this->getCasts()[$key]));
    }
    protected function isCustomDateTimeCast($cast)
    {
        return strncmp($cast, 'date:', 5) === 0 ||
               strncmp($cast, 'datetime:', 9) === 0;
    }
    protected function isDecimalCast($cast)
    {
        return strncmp($cast, 'decimal:', 8) === 0;
    }
    public function setAttribute($key, $value)
    {
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }
        elseif ($value && $this->isDateAttribute($key)) {
            $value = $this->fromDateTime($value);
        }
        if ($this->isJsonCastable($key) && ! is_null($value)) {
            $value = $this->castAttributeAsJson($key, $value);
        }
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }
        $this->attributes[$key] = $value;
        return $this;
    }
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set'.Str::studly($key).'Attribute');
    }
    protected function setMutatedAttributeValue($key, $value)
    {
        return $this->{'set'.Str::studly($key).'Attribute'}($value);
    }
    protected function isDateAttribute($key)
    {
        return in_array($key, $this->getDates()) ||
                                    $this->isDateCastable($key);
    }
    public function fillJsonAttribute($key, $value)
    {
        [$key, $path] = explode('->', $key, 2);
        $this->attributes[$key] = $this->asJson($this->getArrayAttributeWithValue(
            $path, $key, $value
        ));
        return $this;
    }
    protected function getArrayAttributeWithValue($path, $key, $value)
    {
        return tap($this->getArrayAttributeByKey($key), function (&$array) use ($path, $value) {
            Arr::set($array, str_replace('->', '.', $path), $value);
        });
    }
    protected function getArrayAttributeByKey($key)
    {
        return isset($this->attributes[$key]) ?
                    $this->fromJson($this->attributes[$key]) : [];
    }
    protected function castAttributeAsJson($key, $value)
    {
        $value = $this->asJson($value);
        if ($value === false) {
            throw JsonEncodingException::forAttribute(
                $this, $key, json_last_error_msg()
            );
        }
        return $value;
    }
    protected function asJson($value)
    {
        return json_encode($value);
    }
    public function fromJson($value, $asObject = false)
    {
        return json_decode($value, ! $asObject);
    }
    public function fromFloat($value)
    {
        switch ((string) $value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float) $value;
        }
    }
    protected function asDecimal($value, $decimals)
    {
        return number_format($value, $decimals, '.', '');
    }
    protected function asDate($value)
    {
        return $this->asDateTime($value)->startOfDay();
    }
    protected function asDateTime($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if ($value instanceof DateTimeInterface) {
            return new Carbon(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            );
        }
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }
        if ($this->isStandardDateFormat($value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }
        return Carbon::createFromFormat(
            str_replace('.v', '.u', $this->getDateFormat()), $value
        );
    }
    protected function isStandardDateFormat($value)
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }
    public function fromDateTime($value)
    {
        return empty($value) ? $value : $this->asDateTime($value)->format(
            $this->getDateFormat()
        );
    }
    protected function asTimestamp($value)
    {
        return $this->asDateTime($value)->getTimestamp();
    }
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format($this->getDateFormat());
    }
    public function getDates()
    {
        $defaults = [static::CREATED_AT, static::UPDATED_AT];
        return $this->usesTimestamps()
                    ? array_unique(array_merge($this->dates, $defaults))
                    : $this->dates;
    }
    public function getDateFormat()
    {
        return $this->dateFormat ?: $this->getConnection()->getQueryGrammar()->getDateFormat();
    }
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
        return $this;
    }
    public function hasCast($key, $types = null)
    {
        if (array_key_exists($key, $this->getCasts())) {
            return $types ? in_array($this->getCastType($key), (array) $types, true) : true;
        }
        return false;
    }
    public function getCasts()
    {
        if ($this->getIncrementing()) {
            return array_merge([$this->getKeyName() => $this->getKeyType()], $this->casts);
        }
        return $this->casts;
    }
    protected function isDateCastable($key)
    {
        return $this->hasCast($key, ['date', 'datetime']);
    }
    protected function isJsonCastable($key)
    {
        return $this->hasCast($key, ['array', 'json', 'object', 'collection']);
    }
    public function getAttributes()
    {
        return $this->attributes;
    }
    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;
        if ($sync) {
            $this->syncOriginal();
        }
        return $this;
    }
    public function getOriginal($key = null, $default = null)
    {
        return Arr::get($this->original, $key, $default);
    }
    public function only($attributes)
    {
        $results = [];
        foreach (is_array($attributes) ? $attributes : func_get_args() as $attribute) {
            $results[$attribute] = $this->getAttribute($attribute);
        }
        return $results;
    }
    public function syncOriginal()
    {
        $this->original = $this->attributes;
        return $this;
    }
    public function syncOriginalAttribute($attribute)
    {
        return $this->syncOriginalAttributes($attribute);
    }
    public function syncOriginalAttributes($attributes)
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();
        foreach ($attributes as $attribute) {
            $this->original[$attribute] = $this->attributes[$attribute];
        }
        return $this;
    }
    public function syncChanges()
    {
        $this->changes = $this->getDirty();
        return $this;
    }
    public function isDirty($attributes = null)
    {
        return $this->hasChanges(
            $this->getDirty(), is_array($attributes) ? $attributes : func_get_args()
        );
    }
    public function isClean($attributes = null)
    {
        return ! $this->isDirty(...func_get_args());
    }
    public function wasChanged($attributes = null)
    {
        return $this->hasChanges(
            $this->getChanges(), is_array($attributes) ? $attributes : func_get_args()
        );
    }
    protected function hasChanges($changes, $attributes = null)
    {
        if (empty($attributes)) {
            return count($changes) > 0;
        }
        foreach (Arr::wrap($attributes) as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }
        return false;
    }
    public function getDirty()
    {
        $dirty = [];
        foreach ($this->getAttributes() as $key => $value) {
            if (! $this->originalIsEquivalent($key, $value)) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }
    public function getChanges()
    {
        return $this->changes;
    }
    protected function originalIsEquivalent($key, $current)
    {
        if (! array_key_exists($key, $this->original)) {
            return false;
        }
        $original = $this->getOriginal($key);
        if ($current === $original) {
            return true;
        } elseif (is_null($current)) {
            return false;
        } elseif ($this->isDateAttribute($key)) {
            return $this->fromDateTime($current) ===
                   $this->fromDateTime($original);
        } elseif ($this->hasCast($key)) {
            return $this->castAttribute($key, $current) ===
                   $this->castAttribute($key, $original);
        }
        return is_numeric($current) && is_numeric($original)
                && strcmp((string) $current, (string) $original) === 0;
    }
    public function append($attributes)
    {
        $this->appends = array_unique(
            array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
        );
        return $this;
    }
    public function setAppends(array $appends)
    {
        $this->appends = $appends;
        return $this;
    }
    public function getMutatedAttributes()
    {
        $class = static::class;
        if (! isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }
        return static::$mutatorCache[$class];
    }
    public static function cacheMutatedAttributes($class)
    {
        static::$mutatorCache[$class] = collect(static::getMutatorMethods($class))->map(function ($match) {
            return lcfirst(static::$snakeAttributes ? Str::snake($match) : $match);
        })->all();
    }
    protected static function getMutatorMethods($class)
    {
        preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches);
        return $matches[1];
    }
}
