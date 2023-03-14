<?php
namespace Lcobucci\JWT;
use Lcobucci\JWT\Claim\Factory as ClaimFactory;
use Lcobucci\JWT\Parsing\Encoder;
class BuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $encoder;
    protected $claimFactory;
    protected $defaultClaim;
    protected function setUp()
    {
        $this->encoder = $this->getMock(Encoder::class);
        $this->claimFactory = $this->getMock(ClaimFactory::class, [], [], '', false);
        $this->defaultClaim = $this->getMock(Claim::class);
        $this->claimFactory->expects($this->any())
                           ->method('create')
                           ->willReturn($this->defaultClaim);
    }
    private function createBuilder()
    {
        return new Builder($this->encoder, $this->claimFactory);
    }
    public function constructMustInitializeTheAttributes()
    {
        $builder = $this->createBuilder();
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals([], 'claims', $builder);
        $this->assertAttributeEquals(null, 'signature', $builder);
        $this->assertAttributeSame($this->encoder, 'encoder', $builder);
        $this->assertAttributeSame($this->claimFactory, 'claimFactory', $builder);
    }
    public function setAudienceMustChangeTheAudClaim()
    {
        $builder = $this->createBuilder();
        $builder->setAudience('test');
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals(['aud' => $this->defaultClaim], 'claims', $builder);
    }
    public function setAudienceCanReplicateItemOnHeader()
    {
        $builder = $this->createBuilder();
        $builder->setAudience('test', true);
        $this->assertAttributeEquals(['aud' => $this->defaultClaim], 'claims', $builder);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'aud' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setAudienceMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setAudience('test'));
    }
    public function setExpirationMustChangeTheExpClaim()
    {
        $builder = $this->createBuilder();
        $builder->setExpiration('2');
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals(['exp' => $this->defaultClaim], 'claims', $builder);
    }
    public function setExpirationCanReplicateItemOnHeader()
    {
        $builder = $this->createBuilder();
        $builder->setExpiration('2', true);
        $this->assertAttributeEquals(['exp' => $this->defaultClaim], 'claims', $builder);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'exp' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setExpirationMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setExpiration('2'));
    }
    public function setIdMustChangeTheJtiClaim()
    {
        $builder = $this->createBuilder();
        $builder->setId('2');
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals(['jti' => $this->defaultClaim], 'claims', $builder);
    }
    public function setIdCanReplicateItemOnHeader()
    {
        $builder = $this->createBuilder();
        $builder->setId('2', true);
        $this->assertAttributeEquals(['jti' => $this->defaultClaim], 'claims', $builder);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'jti' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setIdMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setId('2'));
    }
    public function setIssuedAtMustChangeTheIatClaim()
    {
        $builder = $this->createBuilder();
        $builder->setIssuedAt('2');
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals(['iat' => $this->defaultClaim], 'claims', $builder);
    }
    public function setIssuedAtCanReplicateItemOnHeader()
    {
        $builder = $this->createBuilder();
        $builder->setIssuedAt('2', true);
        $this->assertAttributeEquals(['iat' => $this->defaultClaim], 'claims', $builder);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'iat' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setIssuedAtMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setIssuedAt('2'));
    }
    public function setIssuerMustChangeTheIssClaim()
    {
        $builder = $this->createBuilder();
        $builder->setIssuer('2');
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals(['iss' => $this->defaultClaim], 'claims', $builder);
    }
    public function setIssuerCanReplicateItemOnHeader()
    {
        $builder = $this->createBuilder();
        $builder->setIssuer('2', true);
        $this->assertAttributeEquals(['iss' => $this->defaultClaim], 'claims', $builder);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'iss' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setIssuerMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setIssuer('2'));
    }
    public function setNotBeforeMustChangeTheNbfClaim()
    {
        $builder = $this->createBuilder();
        $builder->setNotBefore('2');
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals(['nbf' => $this->defaultClaim], 'claims', $builder);
    }
    public function setNotBeforeCanReplicateItemOnHeader()
    {
        $builder = $this->createBuilder();
        $builder->setNotBefore('2', true);
        $this->assertAttributeEquals(['nbf' => $this->defaultClaim], 'claims', $builder);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'nbf' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setNotBeforeMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setNotBefore('2'));
    }
    public function setSubjectMustChangeTheSubClaim()
    {
        $builder = $this->createBuilder();
        $builder->setSubject('2');
        $this->assertAttributeEquals(['alg' => 'none', 'typ' => 'JWT'], 'headers', $builder);
        $this->assertAttributeEquals(['sub' => $this->defaultClaim], 'claims', $builder);
    }
    public function setSubjectCanReplicateItemOnHeader()
    {
        $builder = $this->createBuilder();
        $builder->setSubject('2', true);
        $this->assertAttributeEquals(['sub' => $this->defaultClaim], 'claims', $builder);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'sub' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setSubjectMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setSubject('2'));
    }
    public function setMustConfigureTheGivenClaim()
    {
        $builder = $this->createBuilder();
        $builder->set('userId', 2);
        $this->assertAttributeEquals(['userId' => $this->defaultClaim], 'claims', $builder);
    }
    public function setMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->set('userId', 2));
    }
    public function setHeaderMustConfigureTheGivenClaim()
    {
        $builder = $this->createBuilder();
        $builder->setHeader('userId', 2);
        $this->assertAttributeEquals(
            ['alg' => 'none', 'typ' => 'JWT', 'userId' => $this->defaultClaim],
            'headers',
            $builder
        );
    }
    public function setHeaderMustKeepAFluentInterface()
    {
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->setHeader('userId', 2));
    }
    public function signMustChangeTheSignature()
    {
        $signer = $this->getMock(Signer::class);
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $signer->expects($this->any())
               ->method('sign')
               ->willReturn($signature);
        $builder = $this->createBuilder();
        $builder->sign($signer, 'test');
        $this->assertAttributeSame($signature, 'signature', $builder);
    }
    public function signMustKeepAFluentInterface()
    {
        $signer = $this->getMock(Signer::class);
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $signer->expects($this->any())
               ->method('sign')
               ->willReturn($signature);
        $builder = $this->createBuilder();
        $this->assertSame($builder, $builder->sign($signer, 'test'));
        return $builder;
    }
    public function unsignMustRemoveTheSignature(Builder $builder)
    {
        $builder->unsign();
        $this->assertAttributeSame(null, 'signature', $builder);
    }
    public function unsignMustKeepAFluentInterface(Builder $builder)
    {
        $this->assertSame($builder, $builder->unsign());
    }
    public function setMustRaiseExceptionWhenTokenHasBeenSigned()
    {
        $signer = $this->getMock(Signer::class);
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $signer->expects($this->any())
               ->method('sign')
               ->willReturn($signature);
        $builder = $this->createBuilder();
        $builder->sign($signer, 'test');
        $builder->set('test', 123);
    }
    public function setHeaderMustRaiseExceptionWhenTokenHasBeenSigned()
    {
        $signer = $this->getMock(Signer::class);
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $signer->expects($this->any())
               ->method('sign')
               ->willReturn($signature);
        $builder = $this->createBuilder();
        $builder->sign($signer, 'test');
        $builder->setHeader('test', 123);
    }
    public function getTokenMustReturnANewTokenWithCurrentConfiguration()
    {
        $signature = $this->getMock(Signature::class, [], [], '', false);
        $this->encoder->expects($this->exactly(2))
                      ->method('jsonEncode')
                      ->withConsecutive([['typ'=> 'JWT', 'alg' => 'none']], [['test' => $this->defaultClaim]])
                      ->willReturnOnConsecutiveCalls('1', '2');
        $this->encoder->expects($this->exactly(3))
                      ->method('base64UrlEncode')
                      ->withConsecutive(['1'], ['2'], [$signature])
                      ->willReturnOnConsecutiveCalls('1', '2', '3');
        $builder = $this->createBuilder()->set('test', 123);
        $builderSign = new \ReflectionProperty($builder, 'signature');
        $builderSign->setAccessible(true);
        $builderSign->setValue($builder, $signature);
        $token = $builder->getToken();
        $tokenSign = new \ReflectionProperty($token, 'signature');
        $tokenSign->setAccessible(true);
        $this->assertAttributeEquals(['1', '2', '3'], 'payload', $token);
        $this->assertAttributeEquals($token->getHeaders(), 'headers', $builder);
        $this->assertAttributeEquals($token->getClaims(), 'claims', $builder);
        $this->assertAttributeSame($tokenSign->getValue($token), 'signature', $builder);
    }
}
