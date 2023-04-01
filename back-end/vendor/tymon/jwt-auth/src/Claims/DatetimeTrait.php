<?php
namespace Tymon\JWTAuth\Claims;
use DateInterval;
use DateTimeInterface;
use Tymon\JWTAuth\Support\Utils;
use Tymon\JWTAuth\Exceptions\InvalidClaimException;
trait DatetimeTrait
{
    protected $leeway = 0;
    public function setValue($value)
    {
        if ($value instanceof DateInterval) {
            $value = Utils::now()->add($value);
        }
        if ($value instanceof DateTimeInterface) {
            $value = $value->getTimestamp();
        }
        return parent::setValue($value);
    }
    public function validateCreate($value)
    {
        if (! is_numeric($value)) {
            throw new InvalidClaimException($this);
        }
        return $value;
    }
    protected function isFuture($value)
    {
        return Utils::isFuture($value, $this->leeway);
    }
    protected function isPast($value)
    {
        return Utils::isPast($value, $this->leeway);
    }
    public function setLeeway($leeway)
    {
        $this->leeway = $leeway;
        return $this;
    }
}
