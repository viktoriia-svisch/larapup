<?php
namespace Lcobucci\JWT\Claim;
use Lcobucci\JWT\ValidationData;
class LesserOrEqualsToTest extends \PHPUnit_Framework_TestCase
{
    public function validateShouldReturnTrueWhenValidationDontHaveTheClaim()
    {
        $claim = new LesserOrEqualsTo('iss', 10);
        $this->assertTrue($claim->validate(new ValidationData()));
    }
    public function validateShouldReturnTrueWhenValueIsLesserThanValidationData()
    {
        $claim = new LesserOrEqualsTo('iss', 10);
        $data = new ValidationData();
        $data->setIssuer(11);
        $this->assertTrue($claim->validate($data));
    }
    public function validateShouldReturnTrueWhenValueIsEqualsToValidationData()
    {
        $claim = new LesserOrEqualsTo('iss', 10);
        $data = new ValidationData();
        $data->setIssuer(10);
        $this->assertTrue($claim->validate($data));
    }
    public function validateShouldReturnFalseWhenValueIsGreaterThanValidationData()
    {
        $claim = new LesserOrEqualsTo('iss', 11);
        $data = new ValidationData();
        $data->setIssuer(10);
        $this->assertFalse($claim->validate($data));
    }
}
