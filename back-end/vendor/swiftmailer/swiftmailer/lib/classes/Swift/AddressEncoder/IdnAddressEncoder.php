<?php
class Swift_AddressEncoder_IdnAddressEncoder implements Swift_AddressEncoder
{
    public function encodeString(string $address): string
    {
        $i = strrpos($address, '@');
        if (false !== $i) {
            $local = substr($address, 0, $i);
            $domain = substr($address, $i + 1);
            if (preg_match('/[^\x00-\x7F]/', $local)) {
                throw new Swift_AddressEncoderException('Non-ASCII characters not supported in local-part', $address);
            }
            if (preg_match('/[^\x00-\x7F]/', $domain)) {
                $address = sprintf('%s@%s', $local, $this->idnToAscii($domain));
            }
        }
        return $address;
    }
    protected function idnToAscii(string $string): string
    {
        if (function_exists('idn_to_ascii')) {
            return idn_to_ascii($string, 0, INTL_IDNA_VARIANT_UTS46);
        }
        if (class_exists('TrueBV\Punycode')) {
            $punycode = new \TrueBV\Punycode();
            return $punycode->encode($string);
        }
        throw new Swift_AddressEncoderException('Non-ASCII characters in address, but no IDN encoder found (install the intl extension or the true/punycode package)', $string);
    }
}
