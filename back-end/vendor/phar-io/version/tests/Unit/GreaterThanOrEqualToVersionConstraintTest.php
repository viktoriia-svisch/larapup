<?php
namespace PharIo\Version;
use PHPUnit\Framework\TestCase;
class GreaterThanOrEqualToVersionConstraintTest extends TestCase {
    public function versionProvider() {
        return [
            [new Version('1.0.2'), new Version('1.0.2'), true],
            [new Version('1.0.2'), new Version('1.0.3'), true],
            [new Version('1.0.2'), new Version('1.1.1'), true],
            [new Version('1.0.2'), new Version('2.0.0'), true],
            [new Version('1.0.2'), new Version('1.0.3'), true],
            [new Version('1.0.2'), new Version('1.0.1'), false],
            [new Version('1.9.8'), new Version('0.9.9'), false],
            [new Version('2.3.1'), new Version('2.2.3'), false],
            [new Version('3.0.2'), new Version('2.9.9'), false],
        ];
    }
    public function testReturnsTrueForCompliantVersions(Version $constraintVersion, Version $version, $expectedResult) {
        $constraint = new GreaterThanOrEqualToVersionConstraint('foo', $constraintVersion);
        $this->assertSame($expectedResult, $constraint->complies($version));
    }
}
