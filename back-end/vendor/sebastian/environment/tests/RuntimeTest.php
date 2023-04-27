<?php declare(strict_types=1);
namespace SebastianBergmann\Environment;
use PHPUnit\Framework\TestCase;
final class RuntimeTest extends TestCase
{
    private $env;
    protected function setUp(): void
    {
        $this->env = new Runtime;
    }
    public function testAbilityToCollectCodeCoverageCanBeAssessed(): void
    {
        $this->assertIsBool($this->env->canCollectCodeCoverage());
    }
    public function testBinaryCanBeRetrieved(): void
    {
        $this->assertIsString($this->env->getBinary());
    }
    public function testCanBeDetected(): void
    {
        $this->assertIsBool($this->env->isHHVM());
    }
    public function testCanBeDetected2(): void
    {
        $this->assertIsBool($this->env->isPHP());
    }
    public function testPCOVCanBeDetected(): void
    {
        $this->assertIsBool($this->env->hasPCOV());
    }
    public function testXdebugCanBeDetected(): void
    {
        $this->assertIsBool($this->env->hasXdebug());
    }
    public function testNameAndVersionCanBeRetrieved(): void
    {
        $this->assertIsString($this->env->getNameWithVersion());
    }
    public function testNameCanBeRetrieved(): void
    {
        $this->assertIsString($this->env->getName());
    }
    public function testNameAndCodeCoverageDriverCanBeRetrieved(): void
    {
        $this->assertIsString($this->env->getNameWithVersionAndCodeCoverageDriver());
    }
    public function testVersionCanBeRetrieved(): void
    {
        $this->assertIsString($this->env->getVersion());
    }
    public function testVendorUrlCanBeRetrieved(): void
    {
        $this->assertIsString($this->env->getVendorUrl());
    }
}
