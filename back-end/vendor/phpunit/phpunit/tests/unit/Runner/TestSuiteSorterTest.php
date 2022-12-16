<?php
namespace PHPUnit\Runner;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
class TestSuiteSorterTest extends TestCase
{
    private const IGNORE_DEPENDENCIES  = false;
    private const RESOLVE_DEPENDENCIES = true;
    private const MULTIDEPENDENCYTEST_EXECUTION_ORDER = [
        \MultiDependencyTest::class . '::testOne',
        \MultiDependencyTest::class . '::testTwo',
        \MultiDependencyTest::class . '::testThree',
        \MultiDependencyTest::class . '::testFour',
        \MultiDependencyTest::class . '::testFive',
    ];
    public function testThrowsExceptionWhenUsingInvalidOrderOption(): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter;
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('$order must be one of TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_REVERSED, or TestSuiteSorter::ORDER_RANDOMIZED, or TestSuiteSorter::ORDER_DURATION');
        $sorter->reorderTestsInSuite($suite, -1, false, TestSuiteSorter::ORDER_DEFAULT);
    }
    public function testThrowsExceptionWhenUsingInvalidOrderDefectsOption(): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter;
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('$orderDefects must be one of TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFECTS_FIRST');
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, -1);
    }
    public function testShouldNotAffectEmptyTestSuite(int $order, bool $resolveDependencies, int $orderDefects): void
    {
        $sorter = new TestSuiteSorter;
        $suite  = new TestSuite;
        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, $orderDefects);
        $this->assertEmpty($suite->tests());
        $this->assertEmpty($sorter->getOriginalExecutionOrder());
        $this->assertEmpty($sorter->getExecutionOrder());
    }
    public function testBasicExecutionOrderOptions(int $order, bool $resolveDependencies, array $expectedOrder): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, TestSuiteSorter::ORDER_DEFAULT);
        $this->assertSame(self::MULTIDEPENDENCYTEST_EXECUTION_ORDER, $sorter->getOriginalExecutionOrder());
        $this->assertSame($expectedOrder, $sorter->getExecutionOrder());
    }
    public function testCanSetRandomizationWithASeed(): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter;
        \mt_srand(54321);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_RANDOMIZED, false, TestSuiteSorter::ORDER_DEFAULT);
        $expectedOrder = [
            \MultiDependencyTest::class . '::testTwo',
            \MultiDependencyTest::class . '::testFour',
            \MultiDependencyTest::class . '::testFive',
            \MultiDependencyTest::class . '::testThree',
            \MultiDependencyTest::class . '::testOne',
        ];
        $this->assertSame($expectedOrder, $sorter->getExecutionOrder());
    }
    public function testCanSetRandomizationWithASeedAndResolveDependencies(): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter;
        \mt_srand(54321);
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_RANDOMIZED, true, TestSuiteSorter::ORDER_DEFAULT);
        $expectedOrder = [
            \MultiDependencyTest::class . '::testTwo',
            \MultiDependencyTest::class . '::testFive',
            \MultiDependencyTest::class . '::testOne',
            \MultiDependencyTest::class . '::testThree',
            \MultiDependencyTest::class . '::testFour',
        ];
        $this->assertSame($expectedOrder, $sorter->getExecutionOrder());
    }
    public function testOrderDurationWithoutCache(bool $resolveDependencies, array $expected): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite(
            $suite,
            TestSuiteSorter::ORDER_DURATION,
            $resolveDependencies,
            TestSuiteSorter::ORDER_DEFAULT
        );
        $this->assertSame($expected, $sorter->getExecutionOrder());
    }
    public function orderDurationWithoutCacheProvider(): array
    {
        return [
            'dependency-ignore' => [
                self::IGNORE_DEPENDENCIES,
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'dependency-resolve' => [
                self::RESOLVE_DEPENDENCIES,
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
        ];
    }
    public function testOrderDurationWithCache(bool $resolveDependencies, array $testTimes, array $expected): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $cache = new TestResultCache;
        foreach ($testTimes as $testName => $time) {
            $cache->setTime(\MultiDependencyTest::class . '::' . $testName, $time);
        }
        $sorter = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite(
            $suite,
            TestSuiteSorter::ORDER_DURATION,
            $resolveDependencies,
            TestSuiteSorter::ORDER_DEFAULT
        );
        $this->assertSame($expected, $sorter->getExecutionOrder());
    }
    public function orderDurationWithCacheProvider(): array
    {
        return [
            'duration-same-dependency-ignore' => [
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => 1,
                    'testTwo'   => 1,
                    'testThree' => 1,
                    'testFour'  => 1,
                    'testFive'  => 1,
                ],
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'duration-same-dependency-resolve' => [
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => 1,
                    'testTwo'   => 1,
                    'testThree' => 1,
                    'testFour'  => 1,
                    'testFive'  => 1,
                ],
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'duration-different-dependency-ignore' => [
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => 5,
                    'testTwo'   => 3,
                    'testThree' => 4,
                    'testFour'  => 1,
                    'testFive'  => 2,
                ],
                [
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testOne',
                ],
            ],
            'duration-different-dependency-resolve' => [
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => 5,
                    'testTwo'   => 3,
                    'testThree' => 4,
                    'testFour'  => 1,
                    'testFive'  => 2,
                ],
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                ],
            ],
        ];
    }
    public function testSuiteSorterDefectsOptions(int $order, bool $resolveDependencies, array $runState, array $expected): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\MultiDependencyTest::class);
        $cache = new TestResultCache;
        foreach ($runState as $testName => $data) {
            $cache->setState(\MultiDependencyTest::class . '::' . $testName, $data['state']);
            $cache->setTime(\MultiDependencyTest::class . '::' . $testName, $data['time']);
        }
        $sorter  = new TestSuiteSorter($cache);
        $sorter->reorderTestsInSuite($suite, $order, $resolveDependencies, TestSuiteSorter::ORDER_DEFECTS_FIRST);
        $this->assertSame($expected, $sorter->getExecutionOrder());
    }
    public function commonSorterOptionsProvider(): array
    {
        return [
            'default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'resolve default' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::IGNORE_DEPENDENCIES,
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testOne',
                ],
            ],
            'resolve reverse' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::RESOLVE_DEPENDENCIES,
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                ],
            ],
        ];
    }
    public function defectsSorterOptionsProvider(): array
    {
        return [
            'default, no defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'default, empty result cache' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                ],
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'default, testFive skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                ],
            ],
            'default, testTwo testFive skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 0],
                ],
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                ],
            ],
            'default, testThree skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'default resolve, testThree skipped' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testFive',
                ],
            ],
            'reverse, testThree skipped' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::IGNORE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testFour',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testOne',
                ],
            ],
            'default resolve, testThree skipped, testFive fast' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 0],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                ],
            ],
            'reverse resolve, testThree skipped' => [
                TestSuiteSorter::ORDER_REVERSED,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_SKIPPED, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                ],
            ],
            'depends first, then defects' => [
                TestSuiteSorter::ORDER_DEFAULT,
                self::RESOLVE_DEPENDENCIES,
                [
                    'testOne'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testTwo'   => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testThree' => ['state' => BaseTestRunner::STATUS_PASSED, 'time' => 1],
                    'testFour'  => ['state' => BaseTestRunner::STATUS_FAILURE, 'time' => 1],
                    'testFive'  => ['state' => BaseTestRunner::STATUS_FAILURE, 'time' => 1],
                ],
                [
                    \MultiDependencyTest::class . '::testFive',
                    \MultiDependencyTest::class . '::testOne',
                    \MultiDependencyTest::class . '::testTwo',
                    \MultiDependencyTest::class . '::testThree',
                    \MultiDependencyTest::class . '::testFour',
                ],
            ],
        ];
    }
    public function testCanHandleSuiteWithEmptyTestCase(): void
    {
        $suite = new TestSuite;
        $suite->addTestSuite(\EmptyTestCaseTest::class);
        $sorter = new TestSuiteSorter;
        $sorter->reorderTestsInSuite($suite, TestSuiteSorter::ORDER_DEFAULT, false, TestSuiteSorter::ORDER_DEFAULT);
        $this->assertSame(\EmptyTestCaseTest::class, $suite->tests()[0]->getName());
        $this->assertSame('No tests found in class "EmptyTestCaseTest".', $suite->tests()[0]->tests()[0]->getMessage());
    }
    public function suiteSorterOptionPermutationsProvider(): array
    {
        $orderValues        = [TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_REVERSED, TestSuiteSorter::ORDER_RANDOMIZED];
        $resolveValues      = [false, true];
        $orderDefectsValues = [TestSuiteSorter::ORDER_DEFAULT, TestSuiteSorter::ORDER_DEFECTS_FIRST];
        $data = [];
        foreach ($orderValues as $order) {
            foreach ($resolveValues as $resolve) {
                foreach ($orderDefectsValues as $orderDefects) {
                    $data[] = [$order, $resolve, $orderDefects];
                }
            }
        }
        return $data;
    }
}
