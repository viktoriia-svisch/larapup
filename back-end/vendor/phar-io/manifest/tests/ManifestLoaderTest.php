<?php
namespace PharIo\Manifest;
class ManifestLoaderTest extends \PHPUnit\Framework\TestCase {
    public function testCanBeLoadedFromFile() {
        $this->assertInstanceOf(
            Manifest::class,
            ManifestLoader::fromFile(__DIR__ . '/_fixture/library.xml')
        );
    }
    public function testCanBeLoadedFromString() {
        $this->assertInstanceOf(
            Manifest::class,
            ManifestLoader::fromString(
                file_get_contents(__DIR__ . '/_fixture/library.xml')
            )
        );
    }
    public function testCanBeLoadedFromPhar() {
        $this->assertInstanceOf(
            Manifest::class,
            ManifestLoader::fromPhar(__DIR__ . '/_fixture/test.phar')
        );
    }
    public function testLoadingNonExistingFileThrowsException() {
        $this->expectException(ManifestLoaderException::class);
        ManifestLoader::fromFile('/not/existing');
    }
    public function testLoadingInvalidXmlThrowsException() {
        $this->expectException(ManifestLoaderException::class);
        ManifestLoader::fromString('<?xml version="1.0" ?><broken>');
    }
}
