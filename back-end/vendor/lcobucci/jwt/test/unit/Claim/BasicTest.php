<?php
namespace Lcobucci\JWT\Claim;
class BasicTest extends \PHPUnit_Framework_TestCase
{
    public function constructorShouldConfigureTheAttributes()
    {
        $claim = new Basic('test', 1);
        $this->assertAttributeEquals('test', 'name', $claim);
        $this->assertAttributeEquals(1, 'value', $claim);
    }
    public function getNameShouldReturnTheClaimName()
    {
        $claim = new Basic('test', 1);
        $this->assertEquals('test', $claim->getName());
    }
    public function getValueShouldReturnTheClaimValue()
    {
        $claim = new Basic('test', 1);
        $this->assertEquals(1, $claim->getValue());
    }
    public function jsonSerializeShouldReturnTheClaimValue()
    {
        $claim = new Basic('test', 1);
        $this->assertEquals(1, $claim->jsonSerialize());
    }
    public function toStringShouldReturnTheClaimValue()
    {
        $claim = new Basic('test', 1);
        $this->assertEquals('1', (string) $claim);
    }
}
