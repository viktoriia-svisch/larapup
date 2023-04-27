<?php
namespace PHPUnit\Runner\Filter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
class NameFilterIteratorTest extends TestCase
{
    public function testCaseSensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('BankAccountTest')->accept());
    }
    public function testCaseInsensitiveMatch(): void
    {
        $this->assertTrue($this->createFilter('bankaccounttest')->accept());
    }
    private function createFilter(string $filter): NameFilterIterator
    {
        $suite = new TestSuite;
        $suite->addTest(new \BankAccountTest('testBalanceIsInitiallyZero'));
        $iterator = new NameFilterIterator($suite->getIterator(), $filter);
        $iterator->rewind();
        return $iterator;
    }
}
