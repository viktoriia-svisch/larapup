<?php
class Swift_Mime_HeaderEncoder_Base64HeaderEncoderTest extends \PHPUnit\Framework\TestCase
{
    public function testNameIsB()
    {
        $encoder = new Swift_Mime_HeaderEncoder_Base64HeaderEncoder();
        $this->assertEquals('B', $encoder->getName());
    }
}
