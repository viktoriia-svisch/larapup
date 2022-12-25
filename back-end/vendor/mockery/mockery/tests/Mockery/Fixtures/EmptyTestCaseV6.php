<?php
namespace test\Mockery\Fixtures;
use PHPUnit\Runner\BaseTestRunner;
use \PHPUnit\Framework\TestCase;
class EmptyTestCaseV6 extends TestCase
{
    public function getStatus()
    {
        return BaseTestRunner::STATUS_PASSED;
    }
}
