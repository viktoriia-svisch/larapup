<?php
namespace PHPUnit\Framework;
use Countable;
interface Test extends Countable
{
    public function run(TestResult $result = null): TestResult;
}
