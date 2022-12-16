<?php
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
class Swift_Mime_Headers_MailboxHeader extends Swift_Mime_Headers_AbstractHeader
{
    private $mailboxes = [];
    private $emailValidator;
    private $addressEncoder;
    public function __construct($name, Swift_Mime_HeaderEncoder $encoder, EmailValidator $emailValidator, Swift_AddressEncoder $addressEncoder = null)
    {
        $this->setFieldName($name);
        $this->setEncoder($encoder);
        $this->emailValidator = $emailValidator;
        $this->addressEncoder = $addressEncoder ?? new Swift_AddressEncoder_IdnAddressEncoder();
    }
    public function getFieldType()
    {
        return self::TYPE_MAILBOX;
    }
    public function setFieldBodyModel($model)
    {
        $this->setNameAddresses($model);
    }
    public function getFieldBodyModel()
    {
        return $this->getNameAddresses();
    }
    public function setNameAddresses($mailboxes)
    {
        $this->mailboxes = $this->normalizeMailboxes((array) $mailboxes);
        $this->setCachedValue(null); 
    }
    public function getNameAddressStrings()
    {
        return $this->createNameAddressStrings($this->getNameAddresses());
    }
    public function getNameAddresses()
    {
        return $this->mailboxes;
    }
    public function setAddresses($addresses)
    {
        $this->setNameAddresses(array_values((array) $addresses));
    }
    public function getAddresses()
    {
        return array_keys($this->mailboxes);
    }
    public function removeAddresses($addresses)
    {
        $this->setCachedValue(null);
        foreach ((array) $addresses as $address) {
            unset($this->mailboxes[$address]);
        }
    }
    public function getFieldBody()
    {
        if (null === $this->getCachedValue()) {
            $this->setCachedValue($this->createMailboxListString($this->mailboxes));
        }
        return $this->getCachedValue();
    }
    protected function normalizeMailboxes(array $mailboxes)
    {
        $actualMailboxes = [];
        foreach ($mailboxes as $key => $value) {
            if (is_string($key)) {
                $address = $key;
                $name = $value;
            } else {
                $address = $value;
                $name = null;
            }
            $this->assertValidAddress($address);
            $actualMailboxes[$address] = $name;
        }
        return $actualMailboxes;
    }
    protected function createDisplayNameString($displayName, $shorten = false)
    {
        return $this->createPhrase($this, $displayName, $this->getCharset(), $this->getEncoder(), $shorten);
    }
    protected function createMailboxListString(array $mailboxes)
    {
        return implode(', ', $this->createNameAddressStrings($mailboxes));
    }
    protected function tokenNeedsEncoding($token)
    {
        return preg_match('/[()<>\[\]:;@\,."]/', $token) || parent::tokenNeedsEncoding($token);
    }
    private function createNameAddressStrings(array $mailboxes)
    {
        $strings = [];
        foreach ($mailboxes as $email => $name) {
            $mailboxStr = $this->addressEncoder->encodeString($email);
            if (null !== $name) {
                $nameStr = $this->createDisplayNameString($name, empty($strings));
                $mailboxStr = $nameStr.' <'.$mailboxStr.'>';
            }
            $strings[] = $mailboxStr;
        }
        return $strings;
    }
    private function assertValidAddress($address)
    {
        if (!$this->emailValidator->isValid($address, new RFCValidation())) {
            throw new Swift_RfcComplianceException(
                'Address in mailbox given ['.$address.'] does not comply with RFC 2822, 3.6.2.'
            );
        }
    }
}
