<?php
namespace PharIo\Manifest;
use PharIo\Version\ExactVersionConstraint;
use PHPUnit\Framework\TestCase;
class PhpVersionRequirementTest extends TestCase {
    private $requirement;
    protected function setUp() {
        $this->requirement = new PhpVersionRequirement(new ExactVersionConstraint('7.1.0'));
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(PhpVersionRequirement::class, $this->requirement);
    }
    public function testVersionConstraintCanBeRetrieved() {
        $this->assertEquals('7.1.0', $this->requirement->getVersionConstraint()->asString());
    }
}
