<?php
class Swift_Encoder_QpEncoderTest extends \SwiftMailerTestCase
{
    public function testPermittedCharactersAreNotEncoded()
    {
        foreach (array_merge(range(33, 60), range(62, 126)) as $ordinal) {
            $char = chr($ordinal);
            $charStream = $this->createCharStream();
            $charStream->shouldReceive('flushContents')
                       ->once();
            $charStream->shouldReceive('importString')
                       ->once()
                       ->with($char);
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([$ordinal]);
            $charStream->shouldReceive('readBytes')
                       ->atLeast()->times(1)
                       ->andReturn(false);
            $encoder = new Swift_Encoder_QpEncoder($charStream);
            $this->assertIdenticalBinary($char, $encoder->encodeString($char));
        }
    }
    public function testWhiteSpaceAtLineEndingIsEncoded()
    {
        $HT = chr(0x09); 
        $SPACE = chr(0x20); 
        $string = 'a'.$HT.$HT."\r\n".'b';
        $charStream = $this->createCharStream();
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importString')
                    ->once()
                    ->with($string);
        $charStream->shouldReceive('readBytes')->once()->andReturn([ord('a')]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x09]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x09]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([ord('b')]);
        $charStream->shouldReceive('readBytes')->once()->andReturn(false);
        $encoder = new Swift_Encoder_QpEncoder($charStream);
        $this->assertEquals(
            'a'.$HT.'=09'."\r\n".'b',
            $encoder->encodeString($string)
            );
        $string = 'a'.$SPACE.$SPACE."\r\n".'b';
        $charStream = $this->createCharStream();
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importString')
                    ->once()
                    ->with($string);
        $charStream->shouldReceive('readBytes')->once()->andReturn([ord('a')]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x20]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x20]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([ord('b')]);
        $charStream->shouldReceive('readBytes')->once()->andReturn(false);
        $encoder = new Swift_Encoder_QpEncoder($charStream);
        $this->assertEquals(
            'a'.$SPACE.'=20'."\r\n".'b',
            $encoder->encodeString($string)
            );
    }
    public function testCRLFIsLeftAlone()
    {
        $string = 'a'."\r\n".'b'."\r\n".'c'."\r\n";
        $charStream = $this->createCharStream();
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importString')
                    ->once()
                    ->with($string);
        $charStream->shouldReceive('readBytes')->once()->andReturn([ord('a')]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([ord('b')]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([ord('c')]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0D]);
        $charStream->shouldReceive('readBytes')->once()->andReturn([0x0A]);
        $charStream->shouldReceive('readBytes')->once()->andReturn(false);
        $encoder = new Swift_Encoder_QpEncoder($charStream);
        $this->assertEquals($string, $encoder->encodeString($string));
    }
    public function testLinesLongerThan76CharactersAreSoftBroken()
    {
        $input = str_repeat('a', 140);
        $charStream = $this->createCharStream();
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importString')
                    ->once()
                    ->with($input);
        $output = '';
        for ($i = 0; $i < 140; ++$i) {
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([ord('a')]);
            if (75 == $i) {
                $output .= "=\r\n";
            }
            $output .= 'a';
        }
        $charStream->shouldReceive('readBytes')
                    ->once()
                    ->andReturn(false);
        $encoder = new Swift_Encoder_QpEncoder($charStream);
        $this->assertEquals($output, $encoder->encodeString($input));
    }
    public function testMaxLineLengthCanBeSpecified()
    {
        $input = str_repeat('a', 100);
        $charStream = $this->createCharStream();
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importString')
                    ->once()
                    ->with($input);
        $output = '';
        for ($i = 0; $i < 100; ++$i) {
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([ord('a')]);
            if (53 == $i) {
                $output .= "=\r\n";
            }
            $output .= 'a';
        }
        $charStream->shouldReceive('readBytes')
                    ->once()
                    ->andReturn(false);
        $encoder = new Swift_Encoder_QpEncoder($charStream);
        $this->assertEquals($output, $encoder->encodeString($input, 0, 54));
    }
    public function testBytesBelowPermittedRangeAreEncoded()
    {
        foreach (range(0, 32) as $ordinal) {
            $char = chr($ordinal);
            $charStream = $this->createCharStream();
            $charStream->shouldReceive('flushContents')
                       ->once();
            $charStream->shouldReceive('importString')
                       ->once()
                       ->with($char);
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([$ordinal]);
            $charStream->shouldReceive('readBytes')
                       ->atLeast()->times(1)
                       ->andReturn(false);
            $encoder = new Swift_Encoder_QpEncoder($charStream);
            $this->assertEquals(
                sprintf('=%02X', $ordinal), $encoder->encodeString($char)
                );
        }
    }
    public function testDecimalByte61IsEncoded()
    {
        $char = '=';
        $charStream = $this->createCharStream();
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importString')
                    ->once()
                    ->with($char);
        $charStream->shouldReceive('readBytes')
                    ->once()
                    ->andReturn([61]);
        $charStream->shouldReceive('readBytes')
                    ->atLeast()->times(1)
                    ->andReturn(false);
        $encoder = new Swift_Encoder_QpEncoder($charStream);
        $this->assertEquals('=3D', $encoder->encodeString('='));
    }
    public function testBytesAbovePermittedRangeAreEncoded()
    {
        foreach (range(127, 255) as $ordinal) {
            $char = chr($ordinal);
            $charStream = $this->createCharStream();
            $charStream->shouldReceive('flushContents')
                       ->once();
            $charStream->shouldReceive('importString')
                       ->once()
                       ->with($char);
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([$ordinal]);
            $charStream->shouldReceive('readBytes')
                       ->atLeast()->times(1)
                       ->andReturn(false);
            $encoder = new Swift_Encoder_QpEncoder($charStream);
            $this->assertEquals(
                sprintf('=%02X', $ordinal), $encoder->encodeString($char)
                );
        }
    }
    public function testFirstLineLengthCanBeDifferent()
    {
        $input = str_repeat('a', 140);
        $charStream = $this->createCharStream();
        $charStream->shouldReceive('flushContents')
                    ->once();
        $charStream->shouldReceive('importString')
                    ->once()
                    ->with($input);
        $output = '';
        for ($i = 0; $i < 140; ++$i) {
            $charStream->shouldReceive('readBytes')
                       ->once()
                       ->andReturn([ord('a')]);
            if (53 == $i || 53 + 75 == $i) {
                $output .= "=\r\n";
            }
            $output .= 'a';
        }
        $charStream->shouldReceive('readBytes')
                    ->once()
                    ->andReturn(false);
        $encoder = new Swift_Encoder_QpEncoder($charStream);
        $this->assertEquals(
            $output, $encoder->encodeString($input, 22),
            '%s: First line should start at offset 22 so can only have max length 54'
            );
    }
    public function testTextIsPreWrapped()
    {
        $encoder = $this->createEncoder();
        $input = str_repeat('a', 70)."\r\n".
                 str_repeat('a', 70)."\r\n".
                 str_repeat('a', 70);
        $this->assertEquals(
            $input, $encoder->encodeString($input)
            );
    }
    private function createCharStream()
    {
        return $this->getMockery('Swift_CharacterStream')->shouldIgnoreMissing();
    }
    private function createEncoder()
    {
        $factory = new Swift_CharacterReaderFactory_SimpleCharacterReaderFactory();
        $charStream = new Swift_CharacterStream_NgCharacterStream($factory, 'utf-8');
        return new Swift_Encoder_QpEncoder($charStream);
    }
}
