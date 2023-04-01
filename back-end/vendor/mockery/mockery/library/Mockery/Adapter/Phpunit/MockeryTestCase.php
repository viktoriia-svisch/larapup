<?php
namespace Mockery\Adapter\Phpunit;
if (class_exists('PHPUnit_Framework_TestCase') || version_compare(\PHPUnit\Runner\Version::id(), '8.0.0', '<')) {
    class_alias(MockeryTestCaseSetUpForV7AndPrevious::class, MockeryTestCaseSetUp::class);
} else {
    class_alias(MockeryTestCaseSetUpForV8::class, MockeryTestCaseSetUp::class);
}
abstract class MockeryTestCase extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration, MockeryTestCaseSetUp;
    protected function mockeryTestSetUp()
    {
    }
    protected function mockeryTestTearDown()
    {
    }
}
