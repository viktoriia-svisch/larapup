<?php
namespace Illuminate\Http\Resources\Json;
use ArrayAccess;
use JsonSerializable;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Resources\DelegatesToResource;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
class JsonResource implements ArrayAccess, JsonSerializable, Responsable, UrlRoutable
{
    use ConditionallyLoadsAttributes, DelegatesToResource;
    public $resource;
    public $with = [];
    public $additional = [];
    public static $wrap = 'data';
    public function __construct($resource)
    {
        $this->resource = $resource;
    }
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }
    public static function collection($resource)
    {
        return new AnonymousResourceCollection($resource, static::class);
    }
    public function resolve($request = null)
    {
        $data = $this->toArray(
            $request = $request ?: Container::getInstance()->make('request')
        );
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }
        return $this->filter((array) $data);
    }
    public function toArray($request)
    {
        if (is_null($this->resource)) {
            return [];
        }
        return is_array($this->resource)
            ? $this->resource
            : $this->resource->toArray();
    }
    public function with($request)
    {
        return $this->with;
    }
    public function additional(array $data)
    {
        $this->additional = $data;
        return $this;
    }
    public function withResponse($request, $response)
    {
    }
    public static function wrap($value)
    {
        static::$wrap = $value;
    }
    public static function withoutWrapping()
    {
        static::$wrap = null;
    }
    public function response($request = null)
    {
        return $this->toResponse(
            $request ?: Container::getInstance()->make('request')
        );
    }
    public function toResponse($request)
    {
        return (new ResourceResponse($this))->toResponse($request);
    }
    public function jsonSerialize()
    {
        return $this->resolve(Container::getInstance()->make('request'));
    }
}
