<?php
namespace Illuminate\Validation;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
class ValidationData
{
    public static function initializeAndGatherData($attribute, $masterData)
    {
        $data = Arr::dot(static::initializeAttributeOnData($attribute, $masterData));
        return array_merge($data, static::extractValuesForWildcards(
            $masterData, $data, $attribute
        ));
    }
    protected static function initializeAttributeOnData($attribute, $masterData)
    {
        $explicitPath = static::getLeadingExplicitAttributePath($attribute);
        $data = static::extractDataFromPath($explicitPath, $masterData);
        if (! Str::contains($attribute, '*') || Str::endsWith($attribute, '*')) {
            return $data;
        }
        return data_set($data, $attribute, null, true);
    }
    protected static function extractValuesForWildcards($masterData, $data, $attribute)
    {
        $keys = [];
        $pattern = str_replace('\*', '[^\.]+', preg_quote($attribute));
        foreach ($data as $key => $value) {
            if ((bool) preg_match('/^'.$pattern.'/', $key, $matches)) {
                $keys[] = $matches[0];
            }
        }
        $keys = array_unique($keys);
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = Arr::get($masterData, $key);
        }
        return $data;
    }
    public static function extractDataFromPath($attribute, $masterData)
    {
        $results = [];
        $value = Arr::get($masterData, $attribute, '__missing__');
        if ($value !== '__missing__') {
            Arr::set($results, $attribute, $value);
        }
        return $results;
    }
    public static function getLeadingExplicitAttributePath($attribute)
    {
        return rtrim(explode('*', $attribute)[0], '.') ?: null;
    }
}
