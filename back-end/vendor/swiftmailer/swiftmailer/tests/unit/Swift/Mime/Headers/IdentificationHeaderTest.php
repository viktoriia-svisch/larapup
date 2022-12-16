<?php
use Egulias\EmailValidator\EmailValidator;
class Swift_Mime_Headers_IdentificationHeaderTest extends \PHPUnit\Framework\TestCase
{
    public function testTypeIsIdHeader()
    {
        $header = $this->getHeader('Message-ID');
        $this->assertEquals(Swift_Mime_Header::TYPE_ID, $header->getFieldType());
    }
    public function testValueMatchesMsgIdSpec()
    {
        $header = $this->getHeader('Message-ID');
        $header->setId('id-left@id-right');
        $this->assertEquals('<id-left@id-right>', $header->getFieldBody());
    }
    public function testIdCanBeRetrievedVerbatim()
    {
        $header = $this->getHeader('Message-ID');
        $header->setId('id-left@id-right');
        $this->assertEquals('id-left@id-right', $header->getId());
    }
    public function testMultipleIdsCanBeSet()
    {
        $header = $this->getHeader('References');
        $header->setIds(['a@b', 'x@y']);
        $this->assertEquals(['a@b', 'x@y'], $header->getIds());
    }
    public function testSettingMultipleIdsProducesAListValue()
    {
        $header = $this->getHeader('References');
        $header->setIds(['a@b', 'x@y']);
        $this->assertEquals('<a@b> <x@y>', $header->getFieldBody());
    }
    public function testIdLeftCanBeQuoted()
    {
        $header = $this->getHeader('References');
        $header->setId('"ab"@c');
        $this->assertEquals('"ab"@c', $header->getId());
        $this->assertEquals('<"ab"@c>', $header->getFieldBody());
    }
    public function testIdLeftCanContainAnglesAsQuotedPairs()
    {
        $header = $this->getHeader('References');
        $header->setId('"a\\<\\>b"@c');
        $this->assertEquals('"a\\<\\>b"@c', $header->getId());
        $this->assertEquals('<"a\\<\\>b"@c>', $header->getFieldBody());
    }
    public function testIdLeftCanBeDotAtom()
    {
        $header = $this->getHeader('References');
        $header->setId('a.b+&%$.c@d');
        $this->assertEquals('a.b+&%$.c@d', $header->getId());
        $this->assertEquals('<a.b+&%$.c@d>', $header->getFieldBody());
    }
    public function testInvalidIdLeftThrowsException()
    {
        $header = $this->getHeader('References');
        $header->setId('a b c@d');
    }
    public function testIdRightCanBeDotAtom()
    {
        $header = $this->getHeader('References');
        $header->setId('a@b.c+&%$.d');
        $this->assertEquals('a@b.c+&%$.d', $header->getId());
        $this->assertEquals('<a@b.c+&%$.d>', $header->getFieldBody());
    }
    public function testIdRightCanBeLiteral()
    {
        $header = $this->getHeader('References');
        $header->setId('a@[1.2.3.4]');
        $this->assertEquals('a@[1.2.3.4]', $header->getId());
        $this->assertEquals('<a@[1.2.3.4]>', $header->getFieldBody());
    }
    public function testIdRigthIsIdnEncoded()
    {
        $header = $this->getHeader('References');
        $header->setId('a@ä');
        $this->assertEquals('a@ä', $header->getId());
        $this->assertEquals('<a@xn--4ca>', $header->getFieldBody());
    }
    public function testInvalidIdRightThrowsException()
    {
        $header = $this->getHeader('References');
        $header->setId('a@b c d');
    }
    public function testMissingAtSignThrowsException()
    {
        $header = $this->getHeader('References');
        $header->setId('abc');
    }
    public function testSetBodyModel()
    {
        $header = $this->getHeader('Message-ID');
        $header->setFieldBodyModel('a@b');
        $this->assertEquals(['a@b'], $header->getIds());
    }
    public function testGetBodyModel()
    {
        $header = $this->getHeader('Message-ID');
        $header->setId('a@b');
        $this->assertEquals(['a@b'], $header->getFieldBodyModel());
    }
    public function testStringValue()
    {
        $header = $this->getHeader('References');
        $header->setIds(['a@b', 'x@y']);
        $this->assertEquals('References: <a@b> <x@y>'."\r\n", $header->toString());
    }
    private function getHeader($name)
    {
        return new Swift_Mime_Headers_IdentificationHeader($name, new EmailValidator(), new Swift_AddressEncoder_IdnAddressEncoder());
    }
}
