<?php
namespace Lcobucci\JWT\Claim;
use Lcobucci\JWT\ValidationData;
class EqualsToTest extends \PHPUnit_Framework_TestCase
{
    public function validateShouldReturnTrueWhenValidationDontHaveTheClaim()
    {
        $claim = new EqualsTo('iss', 'test');
        $this->assertTrue($claim->validate(new ValidationData()));
    }
    public function validateShouldReturnTrueWhenValueIsEqualsToValidationData()
    {
        $claim = new EqualsTo('iss', 'test');
        $data = new ValidationData();
        $data->setIssuer('test');
        $this->assertTrue($claim->validate($data));
    }
    public function validateShouldReturnFalseWhenValueIsNotEqualsToValidationData()
    {
        $claim = new EqualsTo('iss', 'test');
        $data = new ValidationData();
        $data->setIssuer('test1');
        $this->assertFalse($claim->validate($data));
    }
}
