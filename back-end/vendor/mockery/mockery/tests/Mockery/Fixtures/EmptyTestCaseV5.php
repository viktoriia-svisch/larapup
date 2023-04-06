<?php
namespace test\Mockery\Fixtures;
class EmptyTestCaseV5 extends \PHPUnit_Framework_TestCase
{
    public function getStatus()
    {
        return \PHPUnit_Runner_BaseTestRunner::STATUS_PASSED;
    }
}
