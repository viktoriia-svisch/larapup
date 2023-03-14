<?php
namespace Mockery\Adapter\Phpunit;
use Mockery;
if (class_exists('PHPUnit_Framework_TestCase') || version_compare(\PHPUnit\Runner\Version::id(), '8.0.0', '<')) {
    class_alias(MockeryPHPUnitIntegrationAssertPostConditionsForV7AndPrevious::class, MockeryPHPUnitIntegrationAssertPostConditions::class);
} else {
    class_alias(MockeryPHPUnitIntegrationAssertPostConditionsForV8::class, MockeryPHPUnitIntegrationAssertPostConditions::class);
}
trait MockeryPHPUnitIntegration
{
    use MockeryPHPUnitIntegrationAssertPostConditions;
    protected $mockeryOpen;
    protected function mockeryAssertPostConditions()
    {
        $this->addMockeryExpectationsToAssertionCount();
        $this->checkMockeryExceptions();
        $this->closeMockery();
        parent::assertPostConditions();
    }
    protected function addMockeryExpectationsToAssertionCount()
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
    }
    protected function checkMockeryExceptions()
    {
        if (!method_exists($this, "markAsRisky")) {
            return;
        }
        foreach (Mockery::getContainer()->mockery_thrownExceptions() as $e) {
            if (!$e->dismissed()) {
                $this->markAsRisky();
            }
        }
    }
    protected function closeMockery()
    {
        Mockery::close();
        $this->mockeryOpen = false;
    }
    protected function startMockery()
    {
        $this->mockeryOpen = true;
    }
    protected function purgeMockeryContainer()
    {
        if ($this->mockeryOpen) {
            Mockery::close();
        }
    }
}
