<?php
class Swift_Encoder_Base64EncoderTest extends \PHPUnit\Framework\TestCase
{
    private $encoder;
    protected function setUp()
    {
        $this->encoder = new Swift_Encoder_Base64Encoder();
    }
    public function testInputOutputRatioIs3to4Bytes()
    {
        $this->assertEquals(
            'MTIz', $this->encoder->encodeString('123'),
            '%s: 3 bytes of input should yield 4 bytes of output'
            );
        $this->assertEquals(
            'MTIzNDU2', $this->encoder->encodeString('123456'),
            '%s: 6 bytes in input should yield 8 bytes of output'
            );
        $this->assertEquals(
            'MTIzNDU2Nzg5', $this->encoder->encodeString('123456789'),
            '%s: 9 bytes in input should yield 12 bytes of output'
            );
    }
    public function testPadLength()
    {
        for ($i = 0; $i < 30; ++$i) {
            $input = pack('C', random_int(0, 255));
            $this->assertRegExp(
                '~^[a-zA-Z0-9/\+]{2}==$~', $this->encoder->encodeString($input),
                '%s: A single byte should have 2 bytes of padding'
                );
        }
        for ($i = 0; $i < 30; ++$i) {
            $input = pack('C*', random_int(0, 255), random_int(0, 255));
            $this->assertRegExp(
                '~^[a-zA-Z0-9/\+]{3}=$~', $this->encoder->encodeString($input),
                '%s: Two bytes should have 1 byte of padding'
                );
        }
        for ($i = 0; $i < 30; ++$i) {
            $input = pack('C*', random_int(0, 255), random_int(0, 255), random_int(0, 255));
            $this->assertRegExp(
                '~^[a-zA-Z0-9/\+]{4}$~', $this->encoder->encodeString($input),
                '%s: Three bytes should have no padding'
                );
        }
    }
    public function testMaximumLineLengthIs76Characters()
    {
        $input =
        'abcdefghijklmnopqrstuvwxyz'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        '1234567890'.
        'abcdefghijklmnopqrstuvwxyz'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        '1234567890'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $output =
        'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXpBQk'.
        'NERUZHSElKS0xNTk9QUVJTVFVWV1hZWjEyMzQ1'."\r\n".
        'Njc4OTBhYmNkZWZnaGlqa2xtbm9wcXJzdHV2d3'.
        'h5ekFCQ0RFRkdISUpLTE1OT1BRUlNUVVZXWFla'."\r\n".
        'MTIzNDU2Nzg5MEFCQ0RFRkdISUpLTE1OT1BRUl'.
        'NUVVZXWFla';                                       
        $this->assertEquals(
            $output, $this->encoder->encodeString($input),
            '%s: Lines should be no more than 76 characters'
            );
    }
    public function testMaximumLineLengthCanBeSpecified()
    {
        $input =
        'abcdefghijklmnopqrstuvwxyz'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        '1234567890'.
        'abcdefghijklmnopqrstuvwxyz'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        '1234567890'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $output =
        'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXpBQk'.
        'NERUZHSElKS0'."\r\n".
        'xNTk9QUVJTVFVWV1hZWjEyMzQ1Njc4OTBhYmNk'.
        'ZWZnaGlqa2xt'."\r\n".
        'bm9wcXJzdHV2d3h5ekFCQ0RFRkdISUpLTE1OT1'.
        'BRUlNUVVZXWF'."\r\n".
        'laMTIzNDU2Nzg5MEFCQ0RFRkdISUpLTE1OT1BR'.
        'UlNUVVZXWFla';                                     
        $this->assertEquals(
            $output, $this->encoder->encodeString($input, 0, 50),
            '%s: Lines should be no more than 100 characters'
            );
    }
    public function testFirstLineLengthCanBeDifferent()
    {
        $input =
        'abcdefghijklmnopqrstuvwxyz'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        '1234567890'.
        'abcdefghijklmnopqrstuvwxyz'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
        '1234567890'.
        'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $output =
        'YWJjZGVmZ2hpamtsbW5vcHFyc3R1dnd4eXpBQk'.
        'NERUZHSElKS0xNTk9QU'."\r\n".
        'VJTVFVWV1hZWjEyMzQ1Njc4OTBhYmNkZWZnaGl'.
        'qa2xtbm9wcXJzdHV2d3h5ekFCQ0RFRkdISUpLT'."\r\n".
        'E1OT1BRUlNUVVZXWFlaMTIzNDU2Nzg5MEFCQ0R'.
        'FRkdISUpLTE1OT1BRUlNUVVZXWFla';                    
        $this->assertEquals(
            $output, $this->encoder->encodeString($input, 19),
            '%s: First line offset is 19 so first line should be 57 chars long'
            );
    }
}
