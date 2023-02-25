<?php
class Swift_Mime_ContentEncoder_QpContentEncoderTest extends \SwiftMailerTestCase
{
    public function testNameIsQuotedPrintable()
    {
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder(
            $this->createCharacterStream(true)
            );
        $this->assertEquals('quoted-printable', $encoder->getName());
    }
    public function testPermittedCharactersAreNotEncoded()
    {
        foreach (array_merge(range(33, 60), range(62, 126)) as $ordinal) {
            $char = chr($ordinal);
            $os = $this->createOutputByteStream(true);
            $charStream = $this->createCharacterStream();
            $is = $this->createInputByteStream();
            $collection = new Swift_StreamCollector();
            $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
            $charStream->shouldReceive('flushContents')
                       ->once();
            $charStream->shouldReceive('importByteStream')
                       ->once()
                       ->with($os);
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([$ordinal]);
            $charStream->shouldReceive('readBytes')
                       ->zeroOrMoreTimes()
                       ->andReturn(false);
            $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
            $encoder->encodeByteStream($os, $is);
            $this->assertIdenticalBinary($char, $collection->content);
        }
    }
    public function testLinearWhiteSpaceAtLineEndingIsEncoded()
    {
        $HT = chr(0x09); 
        $SPACE = chr(0x20); 
        $os = $this->createOutputByteStream(true);
        $charStream = $this->createCharacterStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
        $charStream->shouldReceive('flushContents')
                   ->once();
        $charStream->shouldReceive('importByteStream')
                   ->once()
                   ->with($os);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([ord('a')]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x09]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x09]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([ord('b')]);
        $charStream->shouldReceive('readBytes')
                   ->zeroOrMoreTimes()
                   ->andReturn(false);
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
        $encoder->encodeByteStream($os, $is);
        $this->assertEquals("a\t=09\r\nb", $collection->content);
        $os = $this->createOutputByteStream(true);
        $charStream = $this->createCharacterStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
        $charStream->shouldReceive('flushContents')
                   ->once();
        $charStream->shouldReceive('importByteStream')
                   ->once()
                   ->with($os);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([ord('a')]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x20]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x20]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([ord('b')]);
        $charStream->shouldReceive('readBytes')
                   ->zeroOrMoreTimes()
                   ->andReturn(false);
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
        $encoder->encodeByteStream($os, $is);
        $this->assertEquals("a =20\r\nb", $collection->content);
    }
    public function testCRLFIsLeftAlone()
    {
        $os = $this->createOutputByteStream(true);
        $charStream = $this->createCharacterStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
        $charStream->shouldReceive('flushContents')
                   ->once();
        $charStream->shouldReceive('importByteStream')
                   ->once()
                   ->with($os);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([ord('a')]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([ord('b')]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([ord('c')]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')
                   ->once()
                   ->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')
                   ->zeroOrMoreTimes()
                   ->andReturn(false);
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
        $encoder->encodeByteStream($os, $is);
        $this->assertEquals("a\r\nb\r\nc\r\n", $collection->content);
    }
    public function testLinesLongerThan76CharactersAreSoftBroken()
    {
        $os = $this->createOutputByteStream(true);
        $charStream = $this->createCharacterStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
           ->zeroOrMoreTimes()
           ->andReturnUsing($collection);
        $charStream->shouldReceive('flushContents')
                   ->once();
        $charStream->shouldReceive('importByteStream')
                   ->once()
                   ->with($os);
        for ($seq = 0; $seq <= 140; ++$seq) {
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([ord('a')]);
        }
        $charStream->shouldReceive('readBytes')
                   ->zeroOrMoreTimes()
                   ->andReturn(false);
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
        $encoder->encodeByteStream($os, $is);
        $this->assertEquals(str_repeat('a', 75)."=\r\n".str_repeat('a', 66), $collection->content);
    }
    public function testMaxLineLengthCanBeSpecified()
    {
        $os = $this->createOutputByteStream(true);
        $charStream = $this->createCharacterStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
           ->zeroOrMoreTimes()
           ->andReturnUsing($collection);
        $charStream->shouldReceive('flushContents')
                   ->once();
        $charStream->shouldReceive('importByteStream')
                   ->once()
                   ->with($os);
        for ($seq = 0; $seq <= 100; ++$seq) {
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([ord('a')]);
        }
        $charStream->shouldReceive('readBytes')
                   ->zeroOrMoreTimes()
                   ->andReturn(false);
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
        $encoder->encodeByteStream($os, $is, 0, 54);
        $this->assertEquals(str_repeat('a', 53)."=\r\n".str_repeat('a', 48), $collection->content);
    }
    public function testBytesBelowPermittedRangeAreEncoded()
    {
        foreach (range(0, 32) as $ordinal) {
            $char = chr($ordinal);
            $os = $this->createOutputByteStream(true);
            $charStream = $this->createCharacterStream();
            $is = $this->createInputByteStream();
            $collection = new Swift_StreamCollector();
            $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
            $charStream->shouldReceive('flushContents')
                       ->once();
            $charStream->shouldReceive('importByteStream')
                       ->once()
                       ->with($os);
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([$ordinal]);
            $charStream->shouldReceive('readBytes')
                       ->zeroOrMoreTimes()
                       ->andReturn(false);
            $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
            $encoder->encodeByteStream($os, $is);
            $this->assertEquals(sprintf('=%02X', $ordinal), $collection->content);
        }
    }
    public function testDecimalByte61IsEncoded()
    {
        $char = chr(61);
        $os = $this->createOutputByteStream(true);
        $charStream = $this->createCharacterStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
        $charStream->shouldReceive('flushContents')
                       ->once();
        $charStream->shouldReceive('importByteStream')
                       ->once()
                       ->with($os);
        $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([61]);
        $charStream->shouldReceive('readBytes')
                       ->zeroOrMoreTimes()
                       ->andReturn(false);
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
        $encoder->encodeByteStream($os, $is);
        $this->assertEquals(sprintf('=%02X', 61), $collection->content);
    }
    public function testBytesAbovePermittedRangeAreEncoded()
    {
        foreach (range(127, 255) as $ordinal) {
            $char = chr($ordinal);
            $os = $this->createOutputByteStream(true);
            $charStream = $this->createCharacterStream();
            $is = $this->createInputByteStream();
            $collection = new Swift_StreamCollector();
            $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
            $charStream->shouldReceive('flushContents')
                       ->once();
            $charStream->shouldReceive('importByteStream')
                       ->once()
                       ->with($os);
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([$ordinal]);
            $charStream->shouldReceive('readBytes')
                       ->zeroOrMoreTimes()
                       ->andReturn(false);
            $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
            $encoder->encodeByteStream($os, $is);
            $this->assertEquals(sprintf('=%02X', $ordinal), $collection->content);
        }
    }
    public function testFirstLineLengthCanBeDifferent()
    {
        $os = $this->createOutputByteStream(true);
        $charStream = $this->createCharacterStream();
        $is = $this->createInputByteStream();
        $collection = new Swift_StreamCollector();
        $is->shouldReceive('write')
               ->zeroOrMoreTimes()
               ->andReturnUsing($collection);
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importByteStream')
                    ->once()
                    ->with($os);
        for ($seq = 0; $seq <= 140; ++$seq) {
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([ord('a')]);
        }
        $charStream->shouldReceive('readBytes')
                    ->zeroOrMoreTimes()
                    ->andReturn(false);
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
        $encoder->encodeByteStream($os, $is, 22);
        $this->assertEquals(
            str_repeat('a', 53)."=\r\n".str_repeat('a', 75)."=\r\n".str_repeat('a', 13),
            $collection->content
            );
    }
    public function testObserverInterfaceCanChangeCharset()
    {
        $stream = $this->createCharacterStream();
        $stream->shouldReceive('setCharacterSet')
               ->once()
               ->with('windows-1252');
        $encoder = new Swift_Mime_ContentEncoder_QpContentEncoder($stream);
        $encoder->charsetChanged('windows-1252');
    }
    public function testTextIsPreWrapped()
    {
        $encoder = $this->createEncoder();
        $input = str_repeat('a', 70)."\r\n".
                 str_repeat('a', 70)."\r\n".
                 str_repeat('a', 70);
        $os = new Swift_ByteStream_ArrayByteStream();
        $is = new Swift_ByteStream_ArrayByteStream();
        $is->write($input);
        $encoder->encodeByteStream($is, $os);
        $this->assertEquals(
            $input, $os->read(PHP_INT_MAX)
            );
    }
    private function createCharacterStream($stub = false)
    {
        return $this->getMockery('Swift_CharacterStream')->shouldIgnoreMissing();
    }
    private function createEncoder()
    {
        $factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
        $charStream = new Swift_CharacterStream_NgCharacterStream($factory, 'utf-8');
        return new Swift_Mime_ContentEncoder_QpContentEncoder($charStream);
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
