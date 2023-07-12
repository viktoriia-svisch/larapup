<?php
namespace PharIo\Version;
use PHPUnit\Framework\TestCase;
class ExactVersionConstraintTest extends TestCase {
    public function compliantVersionProvider() {
        return [
            ['1.0.2', new Version('1.0.2')],
            ['4.8.9', new Version('4.8.9')],
            ['4.8', new Version('4.8')],
        ];
    }
    public function nonCompliantVersionProvider() {
        return [
            ['1.0.2', new Version('1.0.3')],
            ['4.8.9', new Version('4.7.9')],
            ['4.8', new Version('4.8.5')],
        ];
    }
    public function testReturnsTrueForCompliantVersion($constraintValue, Version $version) {
        $constraint = new ExactVersionConstraint($constraintValue);
        $this->assertTrue($constraint->complies($version));
    }
    public function testReturnsFalseForNonCompliantVersion($constraintValue, Version $version) {
        $constraint = new ExactVersionConstraint($constraintValue);
        $this->assertFalse($constraint->complies($version));
    }
}
