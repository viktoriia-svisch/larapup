<?php
namespace PHPUnit\Framework;
use Iterator;
use IteratorAggregate;
use PHPUnit\Runner\BaseTestRunner;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Util\FileLoader;
use PHPUnit\Util\InvalidArgumentHelper;
use ReflectionClass;
use ReflectionMethod;
use Throwable;
class TestSuite implements Test, SelfDescribing, IteratorAggregate
{
    protected $backupGlobals;
    protected $backupStaticAttributes;
    protected $runTestInSeparateProcess = false;
    protected $name = '';
    protected $groups = [];
    protected $tests = [];
    protected $numTests = -1;
    protected $testCase = false;
    protected $foundClasses = [];
    private $cachedNumTests;
    private $beStrictAboutChangesToGlobalState;
    private $iteratorFilter;
    private $declaredClasses;
    public static function createTest(ReflectionClass $theClass, $name): Test
    {
        $className = $theClass->getName();
        if (!$theClass->isInstantiable()) {
            return self::warning(
                \sprintf('Cannot instantiate class "%s".', $className)
            );
        }
        $backupSettings = \PHPUnit\Util\Test::getBackupSettings(
            $className,
            $name
        );
        $preserveGlobalState = \PHPUnit\Util\Test::getPreserveGlobalStateSettings(
            $className,
            $name
        );
        $runTestInSeparateProcess = \PHPUnit\Util\Test::getProcessIsolationSettings(
            $className,
            $name
        );
        $runClassInSeparateProcess = \PHPUnit\Util\Test::getClassProcessIsolationSettings(
            $className,
            $name
        );
        $constructor = $theClass->getConstructor();
        if ($constructor === null) {
            throw new Exception('No valid test provided.');
        }
        $parameters = $constructor->getParameters();
        if (\count($parameters) < 2) {
            $test = new $className;
        } 
        else {
            try {
                $data = \PHPUnit\Util\Test::getProvidedData(
                    $className,
                    $name
                );
            } catch (IncompleteTestError $e) {
                $message = \sprintf(
                    'Test for %s::%s marked incomplete by data provider',
                    $className,
                    $name
                );
                $_message = $e->getMessage();
                if (!empty($_message)) {
                    $message .= "\n" . $_message;
                }
                $data = self::incompleteTest($className, $name, $message);
            } catch (SkippedTestError $e) {
                $message = \sprintf(
                    'Test for %s::%s skipped by data provider',
                    $className,
                    $name
                );
                $_message = $e->getMessage();
                if (!empty($_message)) {
                    $message .= "\n" . $_message;
                }
                $data = self::skipTest($className, $name, $message);
            } catch (Throwable $t) {
                $message = \sprintf(
                    'The data provider specified for %s::%s is invalid.',
                    $className,
                    $name
                );
                $_message = $t->getMessage();
                if (!empty($_message)) {
                    $message .= "\n" . $_message;
                }
                $data = self::warning($message);
            }
            if (isset($data)) {
                $test = new DataProviderTestSuite(
                    $className . '::' . $name
                );
                if (empty($data)) {
                    $data = self::warning(
                        \sprintf(
                            'No tests found in suite "%s".',
                            $test->getName()
                        )
                    );
                }
                $groups = \PHPUnit\Util\Test::getGroups($className, $name);
                if ($data instanceof WarningTestCase ||
                    $data instanceof SkippedTestCase ||
                    $data instanceof IncompleteTestCase) {
                    $test->addTest($data, $groups);
                } else {
                    foreach ($data as $_dataName => $_data) {
                        $_test = new $className($name, $_data, $_dataName);
                        if ($runTestInSeparateProcess) {
                            $_test->setRunTestInSeparateProcess(true);
                            if ($preserveGlobalState !== null) {
                                $_test->setPreserveGlobalState($preserveGlobalState);
                            }
                        }
                        if ($runClassInSeparateProcess) {
                            $_test->setRunClassInSeparateProcess(true);
                            if ($preserveGlobalState !== null) {
                                $_test->setPreserveGlobalState($preserveGlobalState);
                            }
                        }
                        if ($backupSettings['backupGlobals'] !== null) {
                            $_test->setBackupGlobals(
                                $backupSettings['backupGlobals']
                            );
                        }
                        if ($backupSettings['backupStaticAttributes'] !== null) {
                            $_test->setBackupStaticAttributes(
                                $backupSettings['backupStaticAttributes']
                            );
                        }
                        $test->addTest($_test, $groups);
                    }
                }
            } else {
                $test = new $className;
            }
        }
        if ($test instanceof TestCase) {
            $test->setName($name);
            if ($runTestInSeparateProcess) {
                $test->setRunTestInSeparateProcess(true);
                if ($preserveGlobalState !== null) {
                    $test->setPreserveGlobalState($preserveGlobalState);
                }
            }
            if ($runClassInSeparateProcess) {
                $test->setRunClassInSeparateProcess(true);
                if ($preserveGlobalState !== null) {
                    $test->setPreserveGlobalState($preserveGlobalState);
                }
            }
            if ($backupSettings['backupGlobals'] !== null) {
                $test->setBackupGlobals($backupSettings['backupGlobals']);
            }
            if ($backupSettings['backupStaticAttributes'] !== null) {
                $test->setBackupStaticAttributes(
                    $backupSettings['backupStaticAttributes']
                );
            }
        }
        return $test;
    }
    public static function isTestMethod(ReflectionMethod $method): bool
    {
        if (\strpos($method->name, 'test') === 0) {
            return true;
        }
        $annotations = \PHPUnit\Util\Test::parseAnnotations($method->getDocComment());
        return isset($annotations['test']);
    }
    public function __construct($theClass = '', $name = '')
    {
        $this->declaredClasses = \get_declared_classes();
        $argumentsValid = false;
        if (\is_object($theClass) &&
            $theClass instanceof ReflectionClass) {
            $argumentsValid = true;
        } elseif (\is_string($theClass) &&
            $theClass !== '' &&
            \class_exists($theClass, true)) {
            $argumentsValid = true;
            if ($name == '') {
                $name = $theClass;
            }
            $theClass = new ReflectionClass($theClass);
        } elseif (\is_string($theClass)) {
            $this->setName($theClass);
            return;
        }
        if (!$argumentsValid) {
            throw new Exception;
        }
        if (!$theClass->isSubclassOf(TestCase::class)) {
            $this->setName($theClass);
            return;
        }
        if ($name != '') {
            $this->setName($name);
        } else {
            $this->setName($theClass->getName());
        }
        $constructor = $theClass->getConstructor();
        if ($constructor !== null &&
            !$constructor->isPublic()) {
            $this->addTest(
                self::warning(
                    \sprintf(
                        'Class "%s" has no public constructor.',
                        $theClass->getName()
                    )
                )
            );
            return;
        }
        foreach ($theClass->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() === Assert::class) {
                continue;
            }
            if ($method->getDeclaringClass()->getName() === TestCase::class) {
                continue;
            }
            $this->addTestMethod($theClass, $method);
        }
        if (empty($this->tests)) {
            $this->addTest(
                self::warning(
                    \sprintf(
                        'No tests found in class "%s".',
                        $theClass->getName()
                    )
                )
            );
        }
        $this->testCase = true;
    }
    protected function setUp(): void
    {
    }
    protected function tearDown(): void
    {
    }
    public function toString(): string
    {
        return $this->getName();
    }
    public function addTest(Test $test, $groups = []): void
    {
        $class = new ReflectionClass($test);
        if (!$class->isAbstract()) {
            $this->tests[]  = $test;
            $this->numTests = -1;
            if ($test instanceof self && empty($groups)) {
                $groups = $test->getGroups();
            }
            if (empty($groups)) {
                $groups = ['default'];
            }
            foreach ($groups as $group) {
                if (!isset($this->groups[$group])) {
                    $this->groups[$group] = [$test];
                } else {
                    $this->groups[$group][] = $test;
                }
            }
            if ($test instanceof TestCase) {
                $test->setGroups($groups);
            }
        }
    }
    public function addTestSuite($testClass): void
    {
        if (\is_string($testClass) && \class_exists($testClass)) {
            $testClass = new ReflectionClass($testClass);
        }
        if (!\is_object($testClass)) {
            throw InvalidArgumentHelper::factory(
                1,
                'class name or object'
            );
        }
        if ($testClass instanceof self) {
            $this->addTest($testClass);
        } elseif ($testClass instanceof ReflectionClass) {
            $suiteMethod = false;
            if (!$testClass->isAbstract() && $testClass->hasMethod(BaseTestRunner::SUITE_METHODNAME)) {
                $method = $testClass->getMethod(
                    BaseTestRunner::SUITE_METHODNAME
                );
                if ($method->isStatic()) {
                    $this->addTest(
                        $method->invoke(null, $testClass->getName())
                    );
                    $suiteMethod = true;
                }
            }
            if (!$suiteMethod && !$testClass->isAbstract() && $testClass->isSubclassOf(TestCase::class)) {
                $this->addTest(new self($testClass));
            }
        } else {
            throw new Exception;
        }
    }
    public function addTestFile(string $filename): void
    {
        if (\file_exists($filename) && \substr($filename, -5) == '.phpt') {
            $this->addTest(
                new PhptTestCase($filename)
            );
            return;
        }
        $filename   = FileLoader::checkAndLoad($filename);
        $newClasses = \array_diff(\get_declared_classes(), $this->declaredClasses);
        if (!empty($newClasses)) {
            $this->foundClasses    = \array_merge($newClasses, $this->foundClasses);
            $this->declaredClasses = \get_declared_classes();
        }
        $shortName      = \basename($filename, '.php');
        $shortNameRegEx = '/(?:^|_|\\\\)' . \preg_quote($shortName, '/') . '$/';
        foreach ($this->foundClasses as $i => $className) {
            if (\preg_match($shortNameRegEx, $className)) {
                $class = new ReflectionClass($className);
                if ($class->getFileName() == $filename) {
                    $newClasses = [$className];
                    unset($this->foundClasses[$i]);
                    break;
                }
            }
        }
        foreach ($newClasses as $className) {
            $class = new ReflectionClass($className);
            if (\dirname($class->getFileName()) === __DIR__) {
                continue;
            }
            if (!$class->isAbstract()) {
                if ($class->hasMethod(BaseTestRunner::SUITE_METHODNAME)) {
                    $method = $class->getMethod(
                        BaseTestRunner::SUITE_METHODNAME
                    );
                    if ($method->isStatic()) {
                        $this->addTest($method->invoke(null, $className));
                    }
                } elseif ($class->implementsInterface(Test::class)) {
                    $this->addTestSuite($class);
                }
            }
        }
        $this->numTests = -1;
    }
    public function addTestFiles($fileNames): void
    {
        if (!(\is_array($fileNames) ||
            (\is_object($fileNames) && $fileNames instanceof Iterator))) {
            throw InvalidArgumentHelper::factory(
                1,
                'array or iterator'
            );
        }
        foreach ($fileNames as $filename) {
            $this->addTestFile((string) $filename);
        }
    }
    public function count($preferCache = false): int
    {
        if ($preferCache && $this->cachedNumTests !== null) {
            return $this->cachedNumTests;
        }
        $numTests = 0;
        foreach ($this as $test) {
            $numTests += \count($test);
        }
        $this->cachedNumTests = $numTests;
        return $numTests;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getGroups(): array
    {
        return \array_keys($this->groups);
    }
    public function getGroupDetails()
    {
        return $this->groups;
    }
    public function setGroupDetails(array $groups): void
    {
        $this->groups = $groups;
    }
    public function run(TestResult $result = null): TestResult
    {
        if ($result === null) {
            $result = $this->createResult();
        }
        if (\count($this) == 0) {
            return $result;
        }
        $hookMethods = \PHPUnit\Util\Test::getHookMethods($this->name);
        $result->startTestSuite($this);
        try {
            $this->setUp();
            foreach ($hookMethods['beforeClass'] as $beforeClassMethod) {
                if ($this->testCase === true &&
                    \class_exists($this->name, false) &&
                    \method_exists($this->name, $beforeClassMethod)) {
                    if ($missingRequirements = \PHPUnit\Util\Test::getMissingRequirements($this->name, $beforeClassMethod)) {
                        $this->markTestSuiteSkipped(\implode(\PHP_EOL, $missingRequirements));
                    }
                    \call_user_func([$this->name, $beforeClassMethod]);
                }
            }
        } catch (SkippedTestSuiteError $error) {
            foreach ($this->tests() as $test) {
                $result->startTest($test);
                $result->addFailure($test, $error, 0);
                $result->endTest($test, 0);
            }
            $this->tearDown();
            $result->endTestSuite($this);
            return $result;
        } catch (Throwable $t) {
            foreach ($this->tests() as $test) {
                if ($result->shouldStop()) {
                    break;
                }
                $result->startTest($test);
                $result->addError($test, $t, 0);
                $result->endTest($test, 0);
            }
            $this->tearDown();
            $result->endTestSuite($this);
            return $result;
        }
        foreach ($this as $test) {
            if ($result->shouldStop()) {
                break;
            }
            if ($test instanceof TestCase || $test instanceof self) {
                $test->setBeStrictAboutChangesToGlobalState($this->beStrictAboutChangesToGlobalState);
                $test->setBackupGlobals($this->backupGlobals);
                $test->setBackupStaticAttributes($this->backupStaticAttributes);
                $test->setRunTestInSeparateProcess($this->runTestInSeparateProcess);
            }
            $test->run($result);
        }
        try {
            foreach ($hookMethods['afterClass'] as $afterClassMethod) {
                if ($this->testCase === true && \class_exists($this->name, false) && \method_exists(
                    $this->name,
                    $afterClassMethod
                )) {
                    \call_user_func([$this->name, $afterClassMethod]);
                }
            }
        } catch (Throwable $t) {
            $message = "Exception in {$this->name}::$afterClassMethod" . \PHP_EOL . $t->getMessage();
            $error   = new SyntheticError($message, 0, $t->getFile(), $t->getLine(), $t->getTrace());
            $test    = new \Failure($afterClassMethod);
            $result->startTest($test);
            $result->addFailure($test, $error, 0);
            $result->endTest($test, 0);
        }
        $this->tearDown();
        $result->endTestSuite($this);
        return $result;
    }
    public function setRunTestInSeparateProcess(bool $runTestInSeparateProcess): void
    {
        $this->runTestInSeparateProcess = $runTestInSeparateProcess;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function testAt(int $index)
    {
        if (isset($this->tests[$index])) {
            return $this->tests[$index];
        }
        return false;
    }
    public function tests(): array
    {
        return $this->tests;
    }
    public function setTests(array $tests): void
    {
        $this->tests = $tests;
    }
    public function markTestSuiteSkipped($message = ''): void
    {
        throw new SkippedTestSuiteError($message);
    }
    public function setBeStrictAboutChangesToGlobalState($beStrictAboutChangesToGlobalState): void
    {
        if (null === $this->beStrictAboutChangesToGlobalState && \is_bool($beStrictAboutChangesToGlobalState)) {
            $this->beStrictAboutChangesToGlobalState = $beStrictAboutChangesToGlobalState;
        }
    }
    public function setBackupGlobals($backupGlobals): void
    {
        if (null === $this->backupGlobals && \is_bool($backupGlobals)) {
            $this->backupGlobals = $backupGlobals;
        }
    }
    public function setBackupStaticAttributes($backupStaticAttributes): void
    {
        if (null === $this->backupStaticAttributes && \is_bool($backupStaticAttributes)) {
            $this->backupStaticAttributes = $backupStaticAttributes;
        }
    }
    public function getIterator(): Iterator
    {
        $iterator = new TestSuiteIterator($this);
        if ($this->iteratorFilter !== null) {
            $iterator = $this->iteratorFilter->factory($iterator, $this);
        }
        return $iterator;
    }
    public function injectFilter(Factory $filter): void
    {
        $this->iteratorFilter = $filter;
        foreach ($this as $test) {
            if ($test instanceof self) {
                $test->injectFilter($filter);
            }
        }
    }
    protected function createResult(): TestResult
    {
        return new TestResult;
    }
    protected function addTestMethod(ReflectionClass $class, ReflectionMethod $method): void
    {
        if (!$this->isTestMethod($method)) {
            return;
        }
        $name = $method->getName();
        if (!$method->isPublic()) {
            $this->addTest(
                self::warning(
                    \sprintf(
                        'Test method "%s" in test class "%s" is not public.',
                        $name,
                        $class->getName()
                    )
                )
            );
            return;
        }
        $test = self::createTest($class, $name);
        if ($test instanceof TestCase || $test instanceof DataProviderTestSuite) {
            $test->setDependencies(
                \PHPUnit\Util\Test::getDependencies($class->getName(), $name)
            );
        }
        $this->addTest(
            $test,
            \PHPUnit\Util\Test::getGroups($class->getName(), $name)
        );
    }
    protected static function warning($message): WarningTestCase
    {
        return new WarningTestCase($message);
    }
    protected static function skipTest($class, $methodName, $message): SkippedTestCase
    {
        return new SkippedTestCase($class, $methodName, $message);
    }
    protected static function incompleteTest($class, $methodName, $message): IncompleteTestCase
    {
        return new IncompleteTestCase($class, $methodName, $message);
    }
}
