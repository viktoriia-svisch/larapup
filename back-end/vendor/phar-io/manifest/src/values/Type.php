<?php
namespace PharIo\Manifest;
use PharIo\Version\VersionConstraint;
abstract class Type {
    public static function application() {
        return new Application;
    }
    public static function library() {
        return new Library;
    }
    public static function extension(ApplicationName $application, VersionConstraint $versionConstraint) {
        return new Extension($application, $versionConstraint);
    }
    public function isApplication() {
        return false;
    }
    public function isLibrary() {
        return false;
    }
    public function isExtension() {
        return false;
    }
}
