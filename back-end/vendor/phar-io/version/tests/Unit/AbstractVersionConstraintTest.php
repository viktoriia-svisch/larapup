<?php
namespace PharIo\Version;
use PHPUnit\Framework\TestCase;
class AbstractVersionConstraintTest extends TestCase {
    public function testAsString() {
        $constraint = $this->getMockForAbstractClass(AbstractVersionConstraint::class, ['foo']);
        $this->assertSame('foo', $constraint->asString());
    }
}
