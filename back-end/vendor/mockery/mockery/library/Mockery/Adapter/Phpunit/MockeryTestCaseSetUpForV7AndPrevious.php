<?php
namespace Mockery\Adapter\Phpunit;
trait MockeryTestCaseSetUpForV7AndPrevious
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockeryTestSetUp();
    }
    protected function tearDown()
    {
        $this->mockeryTestTearDown();
        parent::tearDown();
    }
}
