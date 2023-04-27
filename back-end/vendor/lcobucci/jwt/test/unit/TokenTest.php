<?php
namespace Lcobucci\JWT;
use DateInterval;
use DateTime;
use Lcobucci\JWT\Claim\Basic;
use Lcobucci\JWT\Claim\EqualsTo;
use Lcobucci\JWT\Claim\GreaterOrEqualsTo;
use Lcobucci\JWT\Claim\LesserOrEqualsTo;
class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function constructMustInitializeAnEmptyPlainTextTokenWhenNoArgumentsArePassed()
    {
        $token = new Token();
        $this->assertAttributeEquals(['alg' => 'none'], 'headers', $token);
        $this->assertAttributeEquals([], 'claims', $token);
        $this->assertAttributeEquals(null, 'signature', $token);
        $this->assertAttributeEquals(['', ''], 'payload', $token);
    }
    public function hasHeaderMustReturnTrueWhenItIsConfigured()
    {
        $token = new Token(['test' => 'testing']);
        $this->assertTrue($token->hasHeader('test'));
    }
    public function hasHeaderMustReturnFalseWhenItIsNotConfigured()
    {
        $token = new Token(['test' => 'testing']);
        $this->assertFalse($token->hasHeader('testing'));
    }
    public function getHeaderMustRaiseExceptionWhenHeaderIsNotConfigured()
    {
        $token = new Token(['test' => 'testing']);
        $token->getHeader('testing');
    }
    public function getHeaderMustReturnTheDefaultValueWhenIsNotConfigured()
    {
        $token = new Token(['test' => 'testing']);
        $this->assertEquals('blah', $token->getHeader('testing', 'blah'));
    }
    public function getHeaderMustReturnTheRequestedHeader()
    {
        $token = new Token(['test' => 'testing']);
        $this->assertEquals('testing', $token->getHeader('test'));
    }
    public function getHeaderMustReturnValueWhenItIsAReplicatedClaim()
    {
        $token = new Token(['jti' => new EqualsTo('jti', 1)]);
        $this->assertEquals(1, $token->getHeader('jti'));
    }
    public function getHeadersMustReturnTheConfiguredHeader()
    {
        $token = new Token(['test' => 'testing']);
        $this->assertEquals(['test' => 'testing'], $token->getHeaders());
    }
    public function getClaimsMustReturnTheConfiguredClaims()
    {
        $token = new Token([], ['test' => 'testing']);
        $this->assertEquals(['test' => 'testing'], $token->getClaims());
    }
    public function hasClaimMustReturnTrueWhenItIsConfigured()
    {
        $token = new Token([], ['test' => new Basic('test', 'testing')]);
        $this->assertTrue($token->hasClaim('test'));
    }
    public function hasClaimMustReturnFalseWhenItIsNotConfigured()
    {
        $token = new Token([], ['test' => new Basic('test', 'testing')]);
        $this->assertFalse($token->hasClaim('testing'));
    }
    public function getClaimMustReturnTheDefaultValueWhenIsNotConfigured()
    {
        $token = new Token([], ['test' => new Basic('test', 'testing')]);
        $this->assertEquals('blah', $token->getClaim('testing', 'blah'));
    }
    public function getClaimShouldRaiseExceptionWhenClaimIsNotConfigured()
    {
        $token = new Token();
        $token->getClaim('testing');
    }
    public function getClaimShouldReturnTheClaimValueWhenItExists()
    {
        $token = new Token([], ['testing' => new Basic('testing', 'test')]);
        $this->assertEquals('test', $token->getClaim('testing'));
    }
    public function verifyMustRaiseExceptionWhenTokenIsUnsigned()
    {
        $signer = $this->getMock(Signer::class);
        $token = new Token();
        $token->verify($signer, 'test');
    }
    public function verifyShouldReturnFalseWhenTokenAlgorithmIsDifferent()
    {
        $signer = $this->getMock(Signer::class);
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $signer->expects($this->any())
               ->method('getAlgorithmId')
               ->willReturn('HS256');
        $signature->expects($this->never())
                  ->method('verify');
        $token = new Token(['alg' => 'RS256'], [], $signature);
        $this->assertFalse($token->verify($signer, 'test'));
    }
    public function verifyMustDelegateTheValidationToSignature()
    {
        $signer = $this->getMock(Signer::class);
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $signer->expects($this->any())
               ->method('getAlgorithmId')
               ->willReturn('HS256');
        $signature->expects($this->once())
                  ->method('verify')
                  ->with($signer, $this->isType('string'), 'test')
                  ->willReturn(true);
        $token = new Token(['alg' => 'HS256'], [], $signature);
        $this->assertTrue($token->verify($signer, 'test'));
    }
    public function validateShouldReturnTrueWhenClaimsAreEmpty()
    {
        $token = new Token();
        $this->assertTrue($token->validate(new ValidationData()));
    }
    public function validateShouldReturnTrueWhenThereAreNoValidatableClaims()
    {
        $token = new Token([], ['testing' => new Basic('testing', 'test')]);
        $this->assertTrue($token->validate(new ValidationData()));
    }
    public function validateShouldReturnFalseWhenThereIsAtLeastOneFailedValidatableClaim()
    {
        $token = new Token(
            [],
            [
                'iss' => new EqualsTo('iss', 'test'),
                'testing' => new Basic('testing', 'test')
            ]
        );
        $data = new ValidationData();
        $data->setIssuer('test1');
        $this->assertFalse($token->validate($data));
    }
    public function validateShouldReturnTrueWhenThereAreNoFailedValidatableClaims()
    {
        $now = time();
        $token = new Token(
            [],
            [
                'iss' => new EqualsTo('iss', 'test'),
                'iat' => new LesserOrEqualsTo('iat', $now),
                'exp' => new GreaterOrEqualsTo('exp', $now + 500),
                'testing' => new Basic('testing', 'test')
            ]
        );
        $data = new ValidationData($now + 10);
        $data->setIssuer('test');
        $this->assertTrue($token->validate($data));
    }
    public function isExpiredShouldReturnFalseWhenTokenDoesNotExpires()
    {
        $token = new Token(['alg' => 'none']);
        $this->assertFalse($token->isExpired());
    }
    public function isExpiredShouldReturnFalseWhenTokenIsNotExpired()
    {
        $token = new Token(
            ['alg' => 'none'],
            ['exp' => new GreaterOrEqualsTo('exp', time() + 500)]
        );
        $this->assertFalse($token->isExpired());
    }
    public function isExpiredShouldReturnTrueAfterTokenExpires()
    {
        $token = new Token(
            ['alg' => 'none'],
            ['exp' => new GreaterOrEqualsTo('exp', time())]
        );
        $this->assertTrue($token->isExpired(new DateTime('+10 days')));
    }
    public function getPayloadShouldReturnAStringWithTheTwoEncodePartsThatGeneratedTheToken()
    {
        $token = new Token(['alg' => 'none'], [], null, ['test1', 'test2', 'test3']);
        $this->assertEquals('test1.test2', $token->getPayload());
    }
    public function toStringMustReturnEncodedDataWithEmptySignature()
    {
        $token = new Token(['alg' => 'none'], [], null, ['test', 'test']);
        $this->assertEquals('test.test.', (string) $token);
    }
    public function toStringMustReturnEncodedData()
    {
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $token = new Token(['alg' => 'none'], [], $signature, ['test', 'test', 'test']);
        $this->assertEquals('test.test.test', (string) $token);
    }
}
