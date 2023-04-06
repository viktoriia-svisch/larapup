<?php
namespace Lcobucci\JWT\Signer;
class HmacTest extends \PHPUnit_Framework_TestCase
{
    protected $signer;
    protected function setUp()
    {
        $this->signer = $this->getMockForAbstractClass(Hmac::class);
        $this->signer->expects($this->any())
                     ->method('getAlgorithmId')
                     ->willReturn('TEST123');
        $this->signer->expects($this->any())
                     ->method('getAlgorithm')
                     ->willReturn('sha256');
    }
    public function createHashMustReturnAHashAccordingWithTheAlgorithm()
    {
        $hash = hash_hmac('sha256', 'test', '123', true);
        $this->assertEquals($hash, $this->signer->createHash('test', new Key('123')));
        return $hash;
    }
    public function doVerifyShouldReturnTrueWhenExpectedHashWasCreatedWithSameInformation($expected)
    {
        $this->assertTrue($this->signer->doVerify($expected, 'test', new Key('123')));
    }
    public function doVerifyShouldReturnFalseWhenExpectedHashWasNotCreatedWithSameInformation($expected)
    {
        $this->assertFalse($this->signer->doVerify($expected, 'test', new Key('1234')));
    }
    public function doVerifyShouldReturnFalseWhenExpectedHashIsNotString()
    {
        $this->assertFalse($this->signer->doVerify(false, 'test', new Key('1234')));
    }
    public function hashEqualsShouldReturnFalseWhenExpectedHashHasDifferentLengthThanGenerated()
    {
        $this->assertFalse($this->signer->hashEquals('123', '1234'));
    }
    public function hashEqualsShouldReturnFalseWhenExpectedHashIsDifferentThanGenerated($expected)
    {
        $this->assertFalse($this->signer->hashEquals($expected, $this->signer->createHash('test', new Key('1234'))));
    }
    public function hashEqualsShouldReturnTrueWhenExpectedHashIsEqualsThanGenerated($expected)
    {
        $this->assertTrue($this->signer->hashEquals($expected, $this->signer->createHash('test', new Key('123'))));
    }
}
