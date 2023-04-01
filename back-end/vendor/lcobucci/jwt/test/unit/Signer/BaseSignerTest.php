<?php
namespace Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signature;
class BaseSignerTest extends \PHPUnit_Framework_TestCase
{
    protected $signer;
    protected function setUp()
    {
        $this->signer = $this->getMockForAbstractClass(BaseSigner::class);
        $this->signer->method('getAlgorithmId')
                     ->willReturn('TEST123');
    }
    public function modifyHeaderShouldChangeAlgorithm()
    {
        $headers = ['typ' => 'JWT'];
        $this->signer->modifyHeader($headers);
        $this->assertEquals($headers['typ'], 'JWT');
        $this->assertEquals($headers['alg'], 'TEST123');
    }
    public function signMustReturnANewSignature()
    {
        $key = new Key('123');
        $this->signer->expects($this->once())
                     ->method('createHash')
                     ->with('test', $key)
                     ->willReturn('test');
        $this->assertEquals(new Signature('test'), $this->signer->sign('test', $key));
    }
    public function signShouldConvertKeyWhenItsNotAnObject()
    {
        $this->signer->expects($this->once())
                     ->method('createHash')
                     ->with('test', new Key('123'))
                     ->willReturn('test');
        $this->assertEquals(new Signature('test'), $this->signer->sign('test', '123'));
    }
    public function verifyShouldDelegateTheCallToAbstractMethod()
    {
        $key = new Key('123');
        $this->signer->expects($this->once())
                     ->method('doVerify')
                     ->with('test', 'test', $key)
                     ->willReturn(true);
        $this->assertTrue($this->signer->verify('test', 'test', $key));
    }
    public function verifyShouldConvertKeyWhenItsNotAnObject()
    {
        $this->signer->expects($this->once())
                     ->method('doVerify')
                     ->with('test', 'test', new Key('123'))
                     ->willReturn(true);
        $this->assertTrue($this->signer->verify('test', 'test', '123'));
    }
}
