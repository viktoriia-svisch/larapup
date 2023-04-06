<?php
namespace PharIo\Manifest;
class ManifestDocumentMapperTest extends \PHPUnit\Framework\TestCase {
    public function testCanSerializeToString($expected) {
        $manifestDocument = ManifestDocument::fromFile($expected);
        $mapper           = new ManifestDocumentMapper();
        $this->assertInstanceOf(
            Manifest::class,
            $mapper->map($manifestDocument)
        );
    }
    public function dataProvider() {
        return [
            'application' => [__DIR__ . '/_fixture/phpunit-5.6.5.xml'],
            'library'     => [__DIR__ . '/_fixture/library.xml'],
            'extension'   => [__DIR__ . '/_fixture/extension.xml']
        ];
    }
    public function testThrowsExceptionOnUnsupportedType() {
        $manifestDocument = ManifestDocument::fromFile(__DIR__ . '/_fixture/custom.xml');
        $mapper           = new ManifestDocumentMapper();
        $this->expectException(ManifestDocumentMapperException::class);
        $mapper->map($manifestDocument);
    }
    public function testInvalidVersionInformationThrowsException() {
        $manifestDocument = ManifestDocument::fromFile(__DIR__ . '/_fixture/invalidversion.xml');
        $mapper           = new ManifestDocumentMapper();
        $this->expectException(ManifestDocumentMapperException::class);
        $mapper->map($manifestDocument);
    }
    public function testInvalidVersionConstraintThrowsException() {
        $manifestDocument = ManifestDocument::fromFile(__DIR__ . '/_fixture/invalidversionconstraint.xml');
        $mapper           = new ManifestDocumentMapper();
        $this->expectException(ManifestDocumentMapperException::class);
        $mapper->map($manifestDocument);
    }
    public function testInvalidCompatibleConstraintThrowsException() {
        $manifestDocument = ManifestDocument::fromFile(__DIR__ . '/_fixture/extension-invalidcompatible.xml');
        $mapper           = new ManifestDocumentMapper();
        $this->expectException(ManifestDocumentMapperException::class);
        $mapper->map($manifestDocument);
    }
}
