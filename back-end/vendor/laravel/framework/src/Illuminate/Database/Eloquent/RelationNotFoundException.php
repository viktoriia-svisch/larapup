<?php
namespace Illuminate\Database\Eloquent;
use RuntimeException;
class RelationNotFoundException extends RuntimeException
{
    public $model;
    public $relation;
    public static function make($model, $relation)
    {
        $class = get_class($model);
        $instance = new static("Call to undefined relationship [{$relation}] on model [{$class}].");
        $instance->model = $model;
        $instance->relation = $relation;
        return $instance;
    }
}
