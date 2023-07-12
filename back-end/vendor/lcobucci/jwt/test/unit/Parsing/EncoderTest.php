<?php
namespace Lcobucci\JWT\Parsing;
class EncoderTest extends \PHPUnit_Framework_TestCase
{
    public function jsonEncodeMustReturnAJSONString()
    {
        $encoder = new Encoder();
        $this->assertEquals('{"test":"test"}', $encoder->jsonEncode(['test' => 'test']));
    }
    public function jsonEncodeMustRaiseExceptionWhenAnErrorHasOccured()
    {
        $encoder = new Encoder();
        $encoder->jsonEncode("\xB1\x31");
    }
    public function base64UrlEncodeMustReturnAnUrlSafeBase64()
    {
        $data = base64_decode('0MB2wKB+L3yvIdzeggmJ+5WOSLaRLTUPXbpzqUe0yuo=');
        $encoder = new Encoder();
        $this->assertEquals('0MB2wKB-L3yvIdzeggmJ-5WOSLaRLTUPXbpzqUe0yuo', $encoder->base64UrlEncode($data));
    }
}
