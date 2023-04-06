<?php
namespace Illuminate\Database\Eloquent;
use RuntimeException;
class JsonEncodingException extends RuntimeException
{
    public static function forModel($model, $message)
    {
        return new static('Error encoding model ['.get_class($model).'] with ID ['.$model->getKey().'] to JSON: '.$message);
    }
    public static function forAttribute($model, $key, $message)
    {
        $class = get_class($model);
        return new static("Unable to encode attribute [{$key}] for model [{$class}] to JSON: {$message}.");
    }
}
