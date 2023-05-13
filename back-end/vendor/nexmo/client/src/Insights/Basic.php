<?php
namespace Nexmo\Insights;
use Nexmo\Client\Exception\Exception;
use Nexmo\Entity\JsonUnserializableInterface;
class Basic implements \JsonSerializable, JsonUnserializableInterface, \ArrayAccess {
    protected $data = [];
    public function __construct($number)
    {
        $this->data['national_format_number'] = $number;
    }
    public function getRequestId()
    {
        return $this['request_id'];
    }
    public function getNationalFormatNumber()
    {
        return $this['national_format_number'];
    }
    public function getInternationalFormatNumber()
    {
        return $this['international_format_number'];
    }
    public function getCountryCode()
    {
        return $this['country_code'];
    }
    public function getCountryCodeISO3()
    {
        return $this['country_code_iso3'];
    }
    public function getCountryName()
    {
        return $this['country_name'];
    }
    public function getCountryPrefix()
    {
        return $this['country_prefix'];
    }
    public function jsonSerialize()
    {
        return $this->data;
    }
    public function jsonUnserialize(array $json)
    {
        $this->data = $json;
    }
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }
    public function offsetSet($offset, $value)
    {
        throw new Exception('Number insights results are read only');
    }
    public function offsetUnset($offset)
    {
        throw new Exception('Number insights results are read only');
    }
}
