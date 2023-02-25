<?php
namespace Lcobucci\JWT;
class SignatureTest extends \PHPUnit_Framework_TestCase
{
    protected $signer;
    protected function setUp()
    {
        $this->signer = $this->getMock(Signer::class);
    }
    public function constructorMustConfigureAttributes()
    {
        $signature = new Signature('test');
        $this->assertAttributeEquals('test', 'hash', $signature);
    }
    public function toStringMustReturnTheHash()
    {
        $signature = new Signature('test');
        $this->assertEquals('test', (string) $signature);
    }
    public function verifyMustReturnWhatSignerSays()
    {
        $this->signer->expects($this->any())
                     ->method('verify')
                     ->willReturn(true);
        $signature = new Signature('test');
        $this->assertTrue($signature->verify($this->signer, 'one', 'key'));
    }
}
