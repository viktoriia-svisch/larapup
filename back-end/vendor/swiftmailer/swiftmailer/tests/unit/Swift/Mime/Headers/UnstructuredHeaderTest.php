<?php
class Swift_Mime_Headers_UnstructuredHeaderTest extends \SwiftMailerTestCase
{
    private $charset = 'utf-8';
    public function testTypeIsTextHeader()
    {
        $header = $this->getHeader('Subject', $this->getEncoder('Q', true));
        $this->assertEquals(Swift_Mime_Header::TYPE_TEXT, $header->getFieldType());
    }
    public function testGetNameReturnsNameVerbatim()
    {
        $header = $this->getHeader('Subject', $this->getEncoder('Q', true));
        $this->assertEquals('Subject', $header->getFieldName());
    }
    public function testGetValueReturnsValueVerbatim()
    {
        $header = $this->getHeader('Subject', $this->getEncoder('Q', true));
        $header->setValue('Test');
        $this->assertEquals('Test', $header->getValue());
    }
    public function testBasicStructureIsKeyValuePair()
    {
        $header = $this->getHeader('Subject', $this->getEncoder('Q', true));
        $header->setValue('Test');
        $this->assertEquals('Subject: Test'."\r\n", $header->toString());
    }
    public function testLongHeadersAreFoldedAtWordBoundary()
    {
        $value = 'The quick brown fox jumped over the fence, he was a very very '.
            'scary brown fox with a bushy tail';
        $header = $this->getHeader('X-Custom-Header',
            $this->getEncoder('Q', true)
            );
        $header->setValue($value);
        $header->setMaxLineLength(78); 
        $this->assertEquals(
            'X-Custom-Header: The quick brown fox jumped over the fence, he was a'.
            ' very very'."\r\n".
            ' scary brown fox with a bushy tail'."\r\n",
            $header->toString(), '%s: The header should have been folded at 78th char'
            );
    }
    public function testPrintableAsciiOnlyAppearsInHeaders()
    {
        $nonAsciiChar = pack('C', 0x8F);
        $header = $this->getHeader('X-Test', $this->getEncoder('Q', true));
        $header->setValue($nonAsciiChar);
        $this->assertRegExp(
            '~^[^:\x00-\x20\x80-\xFF]+: [^\x80-\xFF\r\n]+\r\n$~s',
            $header->toString()
            );
    }
    public function testEncodedWordsFollowGeneralStructure()
    {
        $nonAsciiChar = pack('C', 0x8F);
        $header = $this->getHeader('X-Test', $this->getEncoder('Q', true));
        $header->setValue($nonAsciiChar);
        $this->assertRegExp(
            '~^X-Test: \=?.*?\?.*?\?.*?\?=\r\n$~s',
            $header->toString()
            );
    }
    public function testEncodedWordIncludesCharsetAndEncodingMethodAndText()
    {
        $nonAsciiChar = pack('C', 0x8F);
        $encoder = $this->getEncoder('Q');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($nonAsciiChar, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn('=8F');
        $header = $this->getHeader('X-Test', $encoder);
        $header->setValue($nonAsciiChar);
        $this->assertEquals(
            'X-Test: =?'.$this->charset.'?Q?=8F?='."\r\n",
            $header->toString()
            );
    }
    public function testEncodedWordsAreUsedToEncodedNonPrintableAscii()
    {
        $nonPrintableBytes = array_merge(
            range(0x00, 0x08), range(0x10, 0x19), [0x7F]
            );
        foreach ($nonPrintableBytes as $byte) {
            $char = pack('C', $byte);
            $encodedChar = sprintf('=%02X', $byte);
            $encoder = $this->getEncoder('Q');
            $encoder->shouldReceive('encodeString')
                ->once()
                ->with($char, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn($encodedChar);
            $header = $this->getHeader('X-A', $encoder);
            $header->setValue($char);
            $this->assertEquals(
                'X-A: =?'.$this->charset.'?Q?'.$encodedChar.'?='."\r\n",
                $header->toString(), '%s: Non-printable ascii should be encoded'
                );
        }
    }
    public function testEncodedWordsAreUsedToEncode8BitOctets()
    {
        foreach (range(0x80, 0xFF) as $byte) {
            $char = pack('C', $byte);
            $encodedChar = sprintf('=%02X', $byte);
            $encoder = $this->getEncoder('Q');
            $encoder->shouldReceive('encodeString')
                ->once()
                ->with($char, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn($encodedChar);
            $header = $this->getHeader('X-A', $encoder);
            $header->setValue($char);
            $this->assertEquals(
                'X-A: =?'.$this->charset.'?Q?'.$encodedChar.'?='."\r\n",
                $header->toString(), '%s: 8-bit octets should be encoded'
                );
        }
    }
    public function testEncodedWordsAreNoMoreThan75CharsPerLine()
    {
        $nonAsciiChar = pack('C', 0x8F);
        $encoder = $this->getEncoder('Q');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($nonAsciiChar, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn('=8F');
        $header = $this->getHeader('X-Test', $encoder);
        $header->setValue($nonAsciiChar);
        $this->assertEquals(
            'X-Test: =?'.$this->charset.'?Q?=8F?='."\r\n",
            $header->toString()
            );
    }
    public function testFWSPIsUsedWhenEncoderReturnsMultipleLines()
    {
        $nonAsciiChar = pack('C', 0x8F);
        $encoder = $this->getEncoder('Q');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($nonAsciiChar, 8, 63, \Mockery::any())
                ->andReturn('line_one_here'."\r\n".'line_two_here');
        $header = $this->getHeader('X-Test', $encoder);
        $header->setValue($nonAsciiChar);
        $this->assertEquals(
            'X-Test: =?'.$this->charset.'?Q?line_one_here?='."\r\n".
            ' =?'.$this->charset.'?Q?line_two_here?='."\r\n",
            $header->toString()
            );
    }
    public function testAdjacentWordsAreEncodedTogether()
    {
        $word = 'w'.pack('C', 0x8F).'rd';
        $text = 'start '.$word.' '.$word.' then end '.$word;
        $encoder = $this->getEncoder('Q');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($word.' '.$word, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn('w=8Frd_w=8Frd');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($word, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn('w=8Frd');
        $header = $this->getHeader('X-Test', $encoder);
        $header->setValue($text);
        $headerString = $header->toString();
        $this->assertEquals('X-Test: start =?'.$this->charset.'?Q?'.
            'w=8Frd_w=8Frd?= then end =?'.$this->charset.'?Q?'.
            'w=8Frd?='."\r\n", $headerString,
            '%s: Adjacent encoded words should appear grouped with WSP encoded'
            );
    }
    public function testLanguageInformationAppearsInEncodedWords()
    {
        $value = 'fo'.pack('C', 0x8F).'bar';
        $encoder = $this->getEncoder('Q');
        $encoder->shouldReceive('encodeString')
                ->once()
                ->with($value, \Mockery::any(), \Mockery::any(), \Mockery::any())
                ->andReturn('fo=8Fbar');
        $header = $this->getHeader('Subject', $encoder);
        $header->setLanguage('en');
        $header->setValue($value);
        $this->assertEquals("Subject: =?utf-8*en?Q?fo=8Fbar?=\r\n",
            $header->toString()
            );
    }
    public function testSetBodyModel()
    {
        $header = $this->getHeader('Subject', $this->getEncoder('Q', true));
        $header->setFieldBodyModel('test');
        $this->assertEquals('test', $header->getValue());
    }
    public function testGetBodyModel()
    {
        $header = $this->getHeader('Subject', $this->getEncoder('Q', true));
        $header->setValue('test');
        $this->assertEquals('test', $header->getFieldBodyModel());
    }
    private function getHeader($name, $encoder)
    {
        $header = new Swift_Mime_Headers_UnstructuredHeader($name, $encoder);
        $header->setCharset($this->charset);
        return $header;
    }
    private function getEncoder($type, $stub = false)
    {
        $encoder = $this->getMockery('Swift_Mime_HeaderEncoder')->shouldIgnoreMissing();
        $encoder->shouldReceive('getName')
                ->zeroOrMoreTimes()
                ->andReturn($type);
        return $encoder;
    }
}
