<?php
namespace PharIo\Version;
use PHPUnit\Framework\TestCase;
class SpecificMajorAndMinorVersionConstraintTest extends TestCase {
    public function versionProvider() {
        return [
            [1, 0, new Version('1.0.2'), true],
            [1, 0, new Version('1.0.3'), true],
            [1, 1, new Version('1.1.1'), true],
            [2, 9, new Version('0.9.9'), false],
            [3, 2, new Version('2.2.3'), false],
            [2, 8, new Version('2.9.9'), false],
        ];
    }
    public function testReturnsTrueForCompliantVersions($major, $minor, Version $version, $expectedResult) {
        $constraint = new SpecificMajorAndMinorVersionConstraint('foo', $major, $minor);
        $this->assertSame($expectedResult, $constraint->complies($version));
    }
}
