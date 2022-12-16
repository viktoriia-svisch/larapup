<?php
namespace Illuminate\Validation;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
class Rule
{
    use Macroable;
    public static function dimensions(array $constraints = [])
    {
        return new Rules\Dimensions($constraints);
    }
    public static function exists($table, $column = 'NULL')
    {
        return new Rules\Exists($table, $column);
    }
    public static function in($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }
        return new Rules\In(is_array($values) ? $values : func_get_args());
    }
    public static function notIn($values)
    {
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }
        return new Rules\NotIn(is_array($values) ? $values : func_get_args());
    }
    public static function requiredIf($callback)
    {
        return new Rules\RequiredIf($callback);
    }
    public static function unique($table, $column = 'NULL')
    {
        return new Rules\Unique($table, $column);
    }
}
