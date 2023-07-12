<?php
namespace Lcobucci\JWT\Signer;
class KeychainTest extends \PHPUnit_Framework_TestCase
{
    public function getPrivateKeyShouldReturnAKey()
    {
        $keychain = new Keychain();
        $key = $keychain->getPrivateKey('testing', 'test');
        $this->assertInstanceOf(Key::class, $key);
        $this->assertAttributeEquals('testing', 'content', $key);
        $this->assertAttributeEquals('test', 'passphrase', $key);
    }
    public function getPublicKeyShouldReturnAValidResource()
    {
        $keychain = new Keychain();
        $key = $keychain->getPublicKey('testing');
        $this->assertInstanceOf(Key::class, $key);
        $this->assertAttributeEquals('testing', 'content', $key);
        $this->assertAttributeEquals(null, 'passphrase', $key);
    }
}
