<?php
namespace Lcobucci\JWT;
class ValidationDataTest extends \PHPUnit_Framework_TestCase
{
    public function constructorShouldConfigureTheItems()
    {
        $expected = $this->createExpectedData();
        $data = new ValidationData(1);
        $this->assertAttributeSame($expected, 'items', $data);
    }
    public function setIdShouldChangeTheId($id)
    {
        $expected = $this->createExpectedData($id);
        $data = new ValidationData(1);
        $data->setId($id);
        $this->assertAttributeSame($expected, 'items', $data);
    }
    public function setIssuerShouldChangeTheIssuer($iss)
    {
        $expected = $this->createExpectedData(null, null, $iss);
        $data = new ValidationData(1);
        $data->setIssuer($iss);
        $this->assertAttributeSame($expected, 'items', $data);
    }
    public function setAudienceShouldChangeTheAudience($aud)
    {
        $expected = $this->createExpectedData(null, null, null, $aud);
        $data = new ValidationData(1);
        $data->setAudience($aud);
        $this->assertAttributeSame($expected, 'items', $data);
    }
    public function setSubjectShouldChangeTheSubject($sub)
    {
        $expected = $this->createExpectedData(null, $sub);
        $data = new ValidationData(1);
        $data->setSubject($sub);
        $this->assertAttributeSame($expected, 'items', $data);
    }
    public function setCurrentTimeShouldChangeTheTimeBasedValues()
    {
        $expected = $this->createExpectedData(null, null, null, null, 2);
        $data = new ValidationData(1);
        $data->setCurrentTime(2);
        $this->assertAttributeSame($expected, 'items', $data);
    }
    public function hasShouldReturnTrueWhenItemIsNotEmpty()
    {
        $data = new ValidationData(1);
        $this->assertTrue($data->has('iat'));
    }
    public function hasShouldReturnFalseWhenItemIsEmpty()
    {
        $data = new ValidationData(1);
        $this->assertFalse($data->has('jti'));
    }
    public function hasShouldReturnFalseWhenItemIsNotDefined()
    {
        $data = new ValidationData(1);
        $this->assertFalse($data->has('test'));
    }
    public function getShouldReturnTheItemValue()
    {
        $data = new ValidationData(1);
        $this->assertEquals(1, $data->get('iat'));
    }
    public function getShouldReturnNullWhenItemIsNotDefined()
    {
        $data = new ValidationData(1);
        $this->assertNull($data->get('test'));
    }
    public function claimValues()
    {
        return [
            [1],
            ['test']
        ];
    }
    private function createExpectedData(
        $id = null,
        $sub = null,
        $iss = null,
        $aud = null,
        $time = 1
    ) {
        return [
            'jti' => $id !== null ? (string) $id : null,
            'iss' => $iss !== null ? (string) $iss : null,
            'aud' => $aud !== null ? (string) $aud : null,
            'sub' => $sub !== null ? (string) $sub : null,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time
        ];
    }
}
