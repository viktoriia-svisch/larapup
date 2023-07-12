<?php
declare(strict_types=1);
namespace Mockery\Adapter\Phpunit;
trait MockeryTestCaseSetUpForV8
{
    protected function setUp() : void
    {
        parent::setUp();
        $this->mockeryTestSetUp();
    }
    protected function tearDown() : void
    {
        $this->mockeryTestTearDown();
        parent::tearDown();
    }
}
