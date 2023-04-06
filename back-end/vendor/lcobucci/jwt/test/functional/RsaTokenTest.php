<?php
namespace Lcobucci\JWT\FunctionalTests;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Keys;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signature;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Rsa\Sha512;
class RsaTokenTest extends \PHPUnit_Framework_TestCase
{
    use Keys;
    private $signer;
    public function createSigner()
    {
        $this->signer = new Sha256();
    }
    public function builderShouldRaiseExceptionWhenKeyIsInvalid()
    {
        $user = (object) ['name' => 'testing', 'email' => 'testing@abc.com'];
        (new Builder())->setId(1)
                       ->setAudience('http:
                       ->setIssuer('http:
                       ->set('user', $user)
                       ->sign($this->signer, new Key('testing'));
    }
    public function builderShouldRaiseExceptionWhenKeyIsNotRsaCompatible()
    {
        $user = (object) ['name' => 'testing', 'email' => 'testing@abc.com'];
        (new Builder())->setId(1)
                       ->setAudience('http:
                       ->setIssuer('http:
                       ->set('user', $user)
                       ->sign($this->signer, static::$ecdsaKeys['private']);
    }
    public function builderCanGenerateAToken()
    {
        $user = (object) ['name' => 'testing', 'email' => 'testing@abc.com'];
        $token = (new Builder())->setId(1)
                              ->setAudience('http:
                              ->setIssuer('http:
                              ->set('user', $user)
                              ->setHeader('jki', '1234')
                              ->sign($this->signer, static::$rsaKeys['private'])
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
        $this->assertFalse($token->verify($this->signer, self::$rsaKeys['encrypted-public']));
    }
    public function verifyShouldReturnFalseWhenAlgorithmIsDifferent(Token $token)
    {
        $this->assertFalse($token->verify(new Sha512(), self::$rsaKeys['public']));
    }
    public function verifyShouldRaiseExceptionWhenKeyIsNotRsaCompatible(Token $token)
    {
        $this->assertFalse($token->verify($this->signer, self::$ecdsaKeys['public1']));
    }
    public function verifyShouldReturnTrueWhenKeyIsRight(Token $token)
    {
        $this->assertTrue($token->verify($this->signer, self::$rsaKeys['public']));
    }
    public function everythingShouldWorkWhenUsingATokenGeneratedByOtherLibs()
    {
        $data = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJoZWxsbyI6IndvcmxkIn0.s'
                . 'GYbB1KrmnESNfJ4D9hOe1Zad_BMyxdb8G4p4LNP7StYlOyBWck6q7XPpPj_6gB'
                . 'Bo1ohD3MA2o0HY42lNIrAStaVhfsFKGdIou8TarwMGZBPcif_3ThUV1pGS3fZc'
                . 'lFwF2SP7rqCngQis_xcUVCyqa8E1Wa_v28grnl1QZrnmQFO8B5JGGLqcrfUHJO'
                . 'nJCupP-Lqh4TmIhftIimSCgLNmJg80wyrpUEfZYReE7hPuEmY0ClTqAGIMQoNS'
                . '98ljwDxwhfbSuL2tAdbV4DekbTpWzspe3dOJ7RSzmPKVZ6NoezaIazKqyqkmHZfcMaHI1lQeGia6LTbHU1bp0gINi74Vw';
        $token = (new Parser())->parse((string) $data);
        $this->assertEquals('world', $token->getClaim('hello'));
        $this->assertTrue($token->verify($this->signer, self::$rsaKeys['public']));
    }
}
