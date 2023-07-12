<?php
namespace Lcobucci\JWT\Claim;
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function constructMustConfigureTheCallbacks()
    {
        $callback = function () {
        };
        $factory = new Factory(['test' => $callback]);
        $expected = [
            'iat' => [$factory, 'createLesserOrEqualsTo'],
            'nbf' => [$factory, 'createLesserOrEqualsTo'],
            'exp' => [$factory, 'createGreaterOrEqualsTo'],
            'iss' => [$factory, 'createEqualsTo'],
            'aud' => [$factory, 'createEqualsTo'],
            'sub' => [$factory, 'createEqualsTo'],
            'jti' => [$factory, 'createEqualsTo'],
            'test' => $callback
        ];
        $this->assertAttributeEquals($expected, 'callbacks', $factory);
    }
    public function createShouldReturnALesserOrEqualsToClaimForIssuedAt()
    {
        $claim = new Factory();
        $this->assertInstanceOf(LesserOrEqualsTo::class, $claim->create('iat', 1));
    }
    public function createShouldReturnALesserOrEqualsToClaimForNotBefore()
    {
        $claim = new Factory();
        $this->assertInstanceOf(LesserOrEqualsTo::class, $claim->create('nbf', 1));
    }
    public function createShouldReturnAGreaterOrEqualsToClaimForExpiration()
    {
        $claim = new Factory();
        $this->assertInstanceOf(GreaterOrEqualsTo::class, $claim->create('exp', 1));
    }
    public function createShouldReturnAnEqualsToClaimForId()
    {
        $claim = new Factory();
        $this->assertInstanceOf(EqualsTo::class, $claim->create('jti', 1));
    }
    public function createShouldReturnAnEqualsToClaimForIssuer()
    {
        $claim = new Factory();
        $this->assertInstanceOf(EqualsTo::class, $claim->create('iss', 1));
    }
    public function createShouldReturnAnEqualsToClaimForAudience()
    {
        $claim = new Factory();
        $this->assertInstanceOf(EqualsTo::class, $claim->create('aud', 1));
    }
    public function createShouldReturnAnEqualsToClaimForSubject()
    {
        $claim = new Factory();
        $this->assertInstanceOf(EqualsTo::class, $claim->create('sub', 1));
    }
    public function createShouldReturnABasiclaimForOtherClaims()
    {
        $claim = new Factory();
        $this->assertInstanceOf(Basic::class, $claim->create('test', 1));
    }
}
