<?php
namespace Lcobucci\JWT\Signer;
use org\bovigo\vfs\vfsStream;
class KeyTest extends \PHPUnit_Framework_TestCase
{
    public function configureRootDir()
    {
        vfsStream::setup(
            'root',
            null,
            [
                'test.pem' => 'testing',
                'emptyFolder' => []
            ]
        );
    }
    public function constructShouldConfigureContentAndPassphrase()
    {
        $key = new Key('testing', 'test');
        $this->assertAttributeEquals('testing', 'content', $key);
        $this->assertAttributeEquals('test', 'passphrase', $key);
    }
    public function constructShouldBeAbleToConfigureContentFromFile()
    {
        $key = new Key('file:
        $this->assertAttributeEquals('testing', 'content', $key);
        $this->assertAttributeEquals(null, 'passphrase', $key);
    }
    public function constructShouldRaiseExceptionWhenFileDoesNotExists()
    {
        new Key('file:
    }
    public function constructShouldRaiseExceptionWhenFileGetContentsFailed()
    {
        new Key('file:
    }
    public function getContentShouldReturnConfiguredData()
    {
        $key = new Key('testing', 'test');
        $this->assertEquals('testing', $key->getContent());
    }
    public function getPassphraseShouldReturnConfiguredData()
    {
        $key = new Key('testing', 'test');
        $this->assertEquals('test', $key->getPassphrase());
    }
}
