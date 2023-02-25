<?php
namespace test\Mockery\Fixtures;
use PHPUnit\Runner\BaseTestRunner;
use \PHPUnit\Framework\TestCase;
class EmptyTestCaseV7 extends TestCase
{
    public function getStatus(): int
    {
        return BaseTestRunner::STATUS_PASSED;
    }
}
