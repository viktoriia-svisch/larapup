<?php
namespace PharIo\Version;
use PHPUnit\Framework\TestCase;
class AnyVersionConstraintTest extends TestCase {
    public function versionProvider() {
        return [
            [new Version('1.0.2')],
            [new Version('4.8')],
            [new Version('0.1.1-dev')]
        ];
    }
    public function testReturnsTrue(Version $version) {
        $constraint = new AnyVersionConstraint;
        $this->assertTrue($constraint->complies($version));
    }
    public function testAsString() {
        $this->assertSame('*', (new AnyVersionConstraint())->asString());
    }
}
