<?php
namespace PharIo\Version;
use PHPUnit\Framework\TestCase;
class SpecificMajorVersionConstraintTest extends TestCase {
    public function versionProvider() {
        return [
            [1, new Version('1.0.2'), true],
            [1, new Version('1.0.3'), true],
            [1, new Version('1.1.1'), true],
            [2, new Version('0.9.9'), false],
            [3, new Version('2.2.3'), false],
            [3, new Version('2.9.9'), false],
        ];
    }
    public function testReturnsTrueForCompliantVersions($major, Version $version, $expectedResult) {
        $constraint = new SpecificMajorVersionConstraint('foo', $major);
        $this->assertSame($expectedResult, $constraint->complies($version));
    }
}
