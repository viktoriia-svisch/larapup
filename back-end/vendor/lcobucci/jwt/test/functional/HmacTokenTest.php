<?php
namespace Lcobucci\JWT\FunctionalTests;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Hmac\Sha512;
class HmacTokenTest extends \PHPUnit_Framework_TestCase
{
    private $signer;
    public function createSigner()
    {
        $this->signer = new Sha256();
    }
    public function builderCanGenerateAToken()
    {
        $user = (object) ['name' => 'testing', 'email' => 'testing@abc.com'];
        $token = (new Builder())->setId(1)
                              ->setAudience('http:
                              ->setIssuer('http:
                              ->set('user', $user)
                              ->setHeader('jki', '1234')
                              ->sign($this->signer, 'testing')
                              ->getToken();
        $this->assertAttributeInstanceOf(Signature::class, 'signature', $token);
        $this->assertEquals('1234', $token->getHeader('jki'));
        $this->assertEquals('http:
        $this->assertEquals('http:
        $this->assertEquals($user, $token->getClaim('user'));
        return $token;
    }
    public function parserCanReadAToken(Token $generated)
    {
        $read = (new Parser())->parse((string) $generated);
        $this->assertEquals($generated, $read);
        $this->assertEquals('testing', $read->getClaim('user')->name);
    }
    public function verifyShouldReturnFalseWhenKeyIsNotRight(Token $token)
    {
        $this->assertFalse($token->verify($this->signer, 'testing1'));
    }
    public function verifyShouldReturnFalseWhenAlgorithmIsDifferent(Token $token)
    {
        $this->assertFalse($token->verify(new Sha512(), 'testing'));
    }
    public function verifyShouldReturnTrueWhenKeyIsRight(Token $token)
    {
        $this->assertTrue($token->verify($this->signer, 'testing'));
    }
    public function everythingShouldWorkWhenUsingATokenGeneratedByOtherLibs()
    {
        $data = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXUyJ9.eyJoZWxsbyI6IndvcmxkIn0.Rh'
                . '7AEgqCB7zae1PkgIlvOpeyw9Ab8NGTbeOH7heHO0o';
        $token = (new Parser())->parse((string) $data);
        $this->assertEquals('world', $token->getClaim('hello'));
        $this->assertTrue($token->verify($this->signer, 'testing'));
    }
}
