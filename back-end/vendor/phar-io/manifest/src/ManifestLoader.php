<?php
namespace PharIo\Manifest;
class ManifestLoader {
    public static function fromFile($filename) {
        try {
            return (new ManifestDocumentMapper())->map(
                ManifestDocument::fromFile($filename)
            );
        } catch (Exception $e) {
            throw new ManifestLoaderException(
                sprintf('Loading %s failed.', $filename),
                $e->getCode(),
                $e
            );
        }
    }
    public static function fromPhar($filename) {
        return self::fromFile('phar:
    }
    public static function fromString($manifest) {
        try {
            return (new ManifestDocumentMapper())->map(
                ManifestDocument::fromString($manifest)
            );
        } catch (Exception $e) {
            throw new ManifestLoaderException(
                'Processing string failed',
                $e->getCode(),
                $e
            );
        }
    }
}
