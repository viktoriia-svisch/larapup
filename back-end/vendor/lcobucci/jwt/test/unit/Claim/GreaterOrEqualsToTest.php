<?php
namespace Lcobucci\JWT\Claim;
use Lcobucci\JWT\ValidationData;
class GreaterOrEqualsToTest extends \PHPUnit_Framework_TestCase
{
    public function validateShouldReturnTrueWhenValidationDontHaveTheClaim()
    {
        $claim = new GreaterOrEqualsTo('iss', 10);
        $this->assertTrue($claim->validate(new ValidationData()));
    }
    public function validateShouldReturnTrueWhenValueIsGreaterThanValidationData()
    {
        $claim = new GreaterOrEqualsTo('iss', 11);
        $data = new ValidationData();
        $data->setIssuer(10);
        $this->assertTrue($claim->validate($data));
    }
    public function validateShouldReturnTrueWhenValueIsEqualsToValidationData()
    {
        $claim = new GreaterOrEqualsTo('iss', 10);
        $data = new ValidationData();
        $data->setIssuer(10);
        $this->assertTrue($claim->validate($data));
    }
    public function validateShouldReturnFalseWhenValueIsLesserThanValidationData()
    {
        $claim = new GreaterOrEqualsTo('iss', 10);
        $data = new ValidationData();
        $data->setIssuer(11);
        $this->assertFalse($claim->validate($data));
    }
}
