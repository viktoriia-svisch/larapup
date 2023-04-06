<?php
namespace PharIo\Manifest;
use PharIo\Version\Version;
class ManifestSerializerTest extends \PHPUnit\Framework\TestCase {
    public function testCanSerializeToString($expected) {
        $manifest = ManifestLoader::fromString($expected);
        $serializer = new ManifestSerializer();
        $this->assertXmlStringEqualsXmlString(
            $expected,
            $serializer->serializeToString($manifest)
        );
    }
    public function dataProvider() {
        return [
            'application' => [file_get_contents(__DIR__ . '/_fixture/phpunit-5.6.5.xml')],
            'library'     => [file_get_contents(__DIR__ . '/_fixture/library.xml')],
            'extension'   => [file_get_contents(__DIR__ . '/_fixture/extension.xml')]
        ];
    }
    public function testCanSerializeToFile() {
        $src        = __DIR__ . '/_fixture/library.xml';
        $dest       = '/tmp/' . uniqid('serializer', true);
        $manifest   = ManifestLoader::fromFile($src);
        $serializer = new ManifestSerializer();
        $serializer->serializeToFile($manifest, $dest);
        $this->assertXmlFileEqualsXmlFile($src, $dest);
        unlink($dest);
    }
    public function testCanHandleUnknownType() {
        $type     = $this->getMockForAbstractClass(Type::class);
        $manifest = new Manifest(
            new ApplicationName('testvendor/testname'),
            new Version('1.0.0'),
            $type,
            new CopyrightInformation(
                new AuthorCollection(),
                new License('bsd-3', new Url('https:
            ),
            new RequirementCollection(),
            new BundledComponentCollection()
        );
        $serializer = new ManifestSerializer();
        $this->assertXmlStringEqualsXmlFile(
            __DIR__ . '/_fixture/custom.xml',
            $serializer->serializeToString($manifest)
        );
    }
}
