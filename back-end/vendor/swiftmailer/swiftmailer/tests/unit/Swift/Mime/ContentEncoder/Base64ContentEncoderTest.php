<?php
class Swift_Mime_ContentEncoder_Base64ContentEncoderTest extends \SwiftMailerTestCase
{
    private $encoder;
    protected function setUp()
    {
        $this->encoder = new Swift_Mime_ContentEncoder_Base64ContentEncoder();
    }
    public function testNameIsBase64()
    {
        $this->assertEquals('base64', $this->encoder->getName());
    }
    public function testInputOutputRatioIs3to4Bytes()
    {
        $os = $this->createOutputByteStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
           ->zeroOrMoreTimes()
           ->andReturnUsing($collection);
        $os->shouldReceive('read')
           ->once()
           ->andReturn('123');
        $os->shouldReceive('read')
           ->zeroOrMoreTimes()
           ->andReturn(false);
        $this->encoder->encodeByteStream($os, $is);
        $this->assertEquals('MTIz', $collection->content);
    }
    public function testPadLength()
    {
        for ($i = 0; $i < 30; ++$i) {
            $os = $this->createOutputByteStream();
            $is = $this->createInputByteStream();
            $collection = new Swift_StreamCollector();
            $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
            $os->shouldReceive('read')
               ->once()
               ->andReturn(pack('C', random_int(0, 255)));
            $os->shouldReceive('read')
               ->zeroOrMoreTimes()
               ->andReturn(false);
            $this->encoder->encodeByteStream($os, $is);
            $this->assertRegExp('~^[a-zA-Z0-9/\+]{2}==$~', $collection->content,
                '%s: A single byte should have 2 bytes of padding'
                );
        }
        for ($i = 0; $i < 30; ++$i) {
            $os = $this->createOutputByteStream();
            $is = $this->createInputByteStream();
            $collection = new Swift_StreamCollector();
            $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
            $os->shouldReceive('read')
               ->once()
               ->andReturn(pack('C*', random_int(0, 255), random_int(0, 255)));
            $os->shouldReceive('read')
               ->zeroOrMoreTimes()
               ->andReturn(false);
            $this->encoder->encodeByteStream($os, $is);
            $this->assertRegExp('~^[a-zA-Z0-9/\+]{3}=$~', $collection->content,
                '%s: Two bytes should have 1 byte of padding'
                );
        }
        for ($i = 0; $i < 30; ++$i) {
            $os = $this->createOutputByteStream();
            $is = $this->createInputByteStream();
            $collection = new Swift_StreamCollector();
            $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
            $os->shouldReceive('read')
               ->once()
               ->andReturn(pack('C*', random_int(0, 255), random_int(0, 255), random_int(0, 255)));
            $os->shouldReceive('read')
               ->zeroOrMoreTimes()
               ->andReturn(false);
            $this->encoder->encodeByteStream($os, $is);
            $this->assertRegExp('~^[a-zA-Z0-9/\+]{4}$~', $collection->content,
                '%s: Three bytes should have no padding'
                );
        }
    }
    public function testMaximumLineLengthIs76Characters()
    {
        $os = $this->createOutputByteStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
           ->zeroOrMoreTimes()
           ->andReturnUsing($collection);
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('mnopqrstuvwx'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('yzabc1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('890ABCDEFGHI'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('JKLMNOPQRSTU'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('VWXYZ1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->zeroOrMoreTimes()
           ->andReturn(false);
        $this->encoder->encodeByteStream($os, $is);
        $this->assertEquals(
            "YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXphYmMxMjM0NTY3ODkwQUJDREVGR0hJSktMTU5PUFFS\r\n".
            'U1RVVldYWVoxMjM0NTY3YWJjZGVmZ2hpamts',
            $collection->content
            );
    }
    public function testMaximumLineLengthCanBeDifferent()
    {
        $os = $this->createOutputByteStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
           ->zeroOrMoreTimes()
           ->andReturnUsing($collection);
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('mnopqrstuvwx'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('yzabc1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('890ABCDEFGHI'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('JKLMNOPQRSTU'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('VWXYZ1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->zeroOrMoreTimes()
           ->andReturn(false);
        $this->encoder->encodeByteStream($os, $is, 0, 50);
        $this->assertEquals(
            "YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXphYmMxMjM0NTY3OD\r\n".
            "kwQUJDREVGR0hJSktMTU5PUFFSU1RVVldYWVoxMjM0NTY3YWJj\r\n".
            'ZGVmZ2hpamts',
            $collection->content
            );
    }
    public function testMaximumLineLengthIsNeverMoreThan76Chars()
    {
        $os = $this->createOutputByteStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
           ->zeroOrMoreTimes()
           ->andReturnUsing($collection);
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('mnopqrstuvwx'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('yzabc1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('890ABCDEFGHI'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('JKLMNOPQRSTU'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('VWXYZ1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->zeroOrMoreTimes()
           ->andReturn(false);
        $this->encoder->encodeByteStream($os, $is, 0, 100);
        $this->assertEquals(
            "YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXphYmMxMjM0NTY3ODkwQUJDREVGR0hJSktMTU5PUFFS\r\n".
            'U1RVVldYWVoxMjM0NTY3YWJjZGVmZ2hpamts',
            $collection->content
            );
    }
    public function testFirstLineLengthCanBeDifferent()
    {
        $os = $this->createOutputByteStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
           ->zeroOrMoreTimes()
           ->andReturnUsing($collection);
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('mnopqrstuvwx'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('yzabc1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('890ABCDEFGHI'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('JKLMNOPQRSTU'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('VWXYZ1234567'); 
        $os->shouldReceive('read')
           ->once()
           ->andReturn('abcdefghijkl'); 
        $os->shouldReceive('read')
           ->zeroOrMoreTimes()
           ->andReturn(false);
        $this->encoder->encodeByteStream($os, $is, 19);
        $this->assertEquals(
            "YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXphYmMxMjM0NTY3ODkwQUJDR\r\n".
            'EVGR0hJSktMTU5PUFFSU1RVVldYWVoxMjM0NTY3YWJjZGVmZ2hpamts',
            $collection->content
            );
    }
    private function createOutputByteStream($stub = false)
    {
        return $this->getMockery('Swift_OutputByteStream')->shouldIgnoreMissing();
    }
    private function createInputByteStream($stub = false)
    {
        return $this->getMockery('Swift_InputByteStream')->shouldIgnoreMissing();
    }
}
