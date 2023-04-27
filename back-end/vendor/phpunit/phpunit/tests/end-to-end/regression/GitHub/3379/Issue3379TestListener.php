<?php declare(strict_types=1);
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
class Issue3379TestListener implements TestListener
{
    use TestListenerDefaultImplementation;
    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
        if ($test instanceof TestCase) {
            print 'Skipped test ' . $test->getName() . ', status: ' . $test->getStatus() . \PHP_EOL;
        }
    }
}
