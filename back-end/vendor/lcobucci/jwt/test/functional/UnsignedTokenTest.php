<?php
namespace Lcobucci\JWT\FunctionalTests;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;
class UnsignedTokenTest extends \PHPUnit_Framework_TestCase
{
    const CURRENT_TIME = 100000;
    public function builderCanGenerateAToken()
    {
        $user = (object) ['name' => 'testing', 'email' => 'testing@abc.com'];
        $token = (new Builder())->setId(1)
                              ->setAudience('http:
                              ->setIssuer('http:
                              ->setExpiration(self::CURRENT_TIME + 3000)
                              ->set('user', $user)
                              ->getToken();
        $this->assertAttributeEquals(null, 'signature', $token);
        $this->assertEquals('http:
        $this->assertEquals('http:
        $this->assertEquals(self::CURRENT_TIME + 3000, $token->getClaim('exp'));
        $this->assertEquals($user, $token->getClaim('user'));
        return $token;
    }
    public function parserCanReadAToken(Token $generated)
    {
        $read = (new Parser())->parse((string) $generated);
        $this->assertEquals($generated, $read);
        $this->assertEquals('testing', $read->getClaim('user')->name);
    }
    public function tokenValidationShouldReturnWhenEverythingIsFine(Token $generated)
    {
        $data = new ValidationData(self::CURRENT_TIME - 10);
        $data->setAudience('http:
        $data->setIssuer('http:
        $this->assertTrue($generated->validate($data));
    }
    public function tokenValidationShouldReturnFalseWhenExpectedDataDontMatch(ValidationData $data, Token $generated)
    {
        $this->assertFalse($generated->validate($data));
    }
    public function invalidValidationData()
    {
        $expired = new ValidationData(self::CURRENT_TIME + 3020);
        $expired->setAudience('http:
        $expired->setIssuer('http:
        $invalidAudience = new ValidationData(self::CURRENT_TIME - 10);
        $invalidAudience->setAudience('http:
        $invalidAudience->setIssuer('http:
        $invalidIssuer = new ValidationData(self::CURRENT_TIME - 10);
        $invalidIssuer->setAudience('http:
        $invalidIssuer->setIssuer('http:
        return [[$expired], [$invalidAudience], [$invalidIssuer]];
    }
}
