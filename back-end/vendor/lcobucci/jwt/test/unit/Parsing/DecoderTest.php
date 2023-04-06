<?php
namespace Lcobucci\JWT\Parsing;
class DecoderTest extends \PHPUnit_Framework_TestCase
{
    public function jsonDecodeMustReturnTheDecodedData()
    {
        $decoder = new Decoder();
        $this->assertEquals(
            (object) ['test' => 'test'],
            $decoder->jsonDecode('{"test":"test"}')
        );
    }
    public function jsonDecodeMustRaiseExceptionWhenAnErrorHasOccured()
    {
        $decoder = new Decoder();
        $decoder->jsonDecode('{"test":\'test\'}');
    }
    public function base64UrlDecodeMustReturnTheRightData()
    {
        $data = base64_decode('0MB2wKB+L3yvIdzeggmJ+5WOSLaRLTUPXbpzqUe0yuo=');
        $decoder = new Decoder();
        $this->assertEquals($data, $decoder->base64UrlDecode('0MB2wKB-L3yvIdzeggmJ-5WOSLaRLTUPXbpzqUe0yuo'));
    }
}
