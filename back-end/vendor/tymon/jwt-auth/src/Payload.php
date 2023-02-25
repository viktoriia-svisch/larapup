<?php
namespace Tymon\JWTAuth;
use Countable;
use ArrayAccess;
use JsonSerializable;
use BadMethodCallException;
use Illuminate\Support\Arr;
use Tymon\JWTAuth\Claims\Claim;
use Tymon\JWTAuth\Claims\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Tymon\JWTAuth\Exceptions\PayloadException;
use Tymon\JWTAuth\Validators\PayloadValidator;
class Payload implements ArrayAccess, Arrayable, Countable, Jsonable, JsonSerializable
{
    private $claims;
    public function __construct(Collection $claims, PayloadValidator $validator, $refreshFlow = false)
    {
        $this->claims = $validator->setRefreshFlow($refreshFlow)->check($claims);
    }
    public function getClaims()
    {
        return $this->claims;
    }
    public function matches(array $values, $strict = false)
    {
        if (empty($values)) {
            return false;
        }
        $claims = $this->getClaims();
        foreach ($values as $key => $value) {
            if (! $claims->has($key) || ! $claims->get($key)->matches($value, $strict)) {
                return false;
            }
        }
        return true;
    }
    public function matchesStrict(array $values)
    {
        return $this->matches($values, true);
    }
    public function get($claim = null)
    {
        $claim = value($claim);
        if ($claim !== null) {
            if (is_array($claim)) {
                return array_map([$this, 'get'], $claim);
            }
            return Arr::get($this->toArray(), $claim);
        }
        return $this->toArray();
    }
    public function getInternal($claim)
    {
        return $this->claims->getByClaimName($claim);
    }
    public function has(Claim $claim)
    {
        return $this->claims->has($claim->getName());
    }
    public function hasKey($claim)
    {
        return $this->offsetExists($claim);
    }
    public function toArray()
    {
        return $this->claims->toPlainArray();
    }
    public function jsonSerialize()
    {
        return $this->toArray();
    }
    public function toJson($options = JSON_UNESCAPED_SLASHES)
    {
        return json_encode($this->toArray(), $options);
    }
    public function __toString()
    {
        return $this->toJson();
    }
    public function offsetExists($key)
    {
        return Arr::has($this->toArray(), $key);
    }
    public function offsetGet($key)
    {
        return Arr::get($this->toArray(), $key);
    }
    public function offsetSet($key, $value)
    {
        throw new PayloadException('The payload is immutable');
    }
    public function offsetUnset($key)
    {
        throw new PayloadException('The payload is immutable');
    }
    public function count()
    {
        return count($this->toArray());
    }
    public function __invoke($claim = null)
    {
        return $this->get($claim);
    }
    public function __call($method, $parameters)
    {
        if (preg_match('/get(.+)\b/i', $method, $matches)) {
            foreach ($this->claims as $claim) {
                if (get_class($claim) === 'Tymon\\JWTAuth\\Claims\\'.$matches[1]) {
                    return $claim->getValue();
                }
            }
        }
        throw new BadMethodCallException(sprintf('The claim [%s] does not exist on the payload.', $method));
    }
}
