<?php
use Mockery\ExpectationInterface;
use Mockery\Generator\CachingGenerator;
use Mockery\Generator\Generator;
use Mockery\Generator\MockConfigurationBuilder;
use Mockery\Generator\StringManipulationGenerator;
use Mockery\Loader\EvalLoader;
use Mockery\Loader\Loader;
use Mockery\Matcher\MatcherAbstract;
use Mockery\ClosureWrapper;
class Mockery
{
    const BLOCKS = 'Mockery_Forward_Blocks';
    protected static $_container = null;
    protected static $_config = null;
    protected static $_generator;
    protected static $_loader;
    private static $_filesToCleanUp = [];
    public static function globalHelpers()
    {
        require_once __DIR__.'/helpers.php';
    }
    public static function builtInTypes()
    {
        $builtInTypes = array(
            'self',
            'array',
            'callable',
            'bool',
            'float',
            'int',
            'string',
            'iterable',
            'void',
        );
        if (version_compare(PHP_VERSION, '7.2.0-dev') >= 0) {
            $builtInTypes[] = 'object';
        }
        return $builtInTypes;
    }
    public static function isBuiltInType($type)
    {
        return in_array($type, \Mockery::builtInTypes());
    }
    public static function mock(...$args)
    {
        return call_user_func_array(array(self::getContainer(), 'mock'), $args);
    }
    public static function spy(...$args)
    {
        if (count($args) && $args[0] instanceof \Closure) {
            $args[0] = new ClosureWrapper($args[0]);
        }
        return call_user_func_array(array(self::getContainer(), 'mock'), $args)->shouldIgnoreMissing();
    }
    public static function instanceMock(...$args)
    {
        return call_user_func_array(array(self::getContainer(), 'mock'), $args);
    }
    public static function namedMock(...$args)
    {
        $name = array_shift($args);
        $builder = new MockConfigurationBuilder();
        $builder->setName($name);
        array_unshift($args, $builder);
        return call_user_func_array(array(self::getContainer(), 'mock'), $args);
    }
    public static function self()
    {
        if (is_null(self::$_container)) {
            throw new \LogicException('You have not declared any mocks yet');
        }
        return self::$_container->self();
    }
    public static function close()
    {
        foreach (self::$_filesToCleanUp as $fileName) {
            @unlink($fileName);
        }
        self::$_filesToCleanUp = [];
        if (is_null(self::$_container)) {
            return;
        }
        $container = self::$_container;
        self::$_container = null;
        $container->mockery_teardown();
        $container->mockery_close();
    }
    public static function fetchMock($name)
    {
        return self::$_container->fetchMock($name);
    }
    public static function getContainer()
    {
        if (is_null(self::$_container)) {
            self::$_container = new Mockery\Container(self::getGenerator(), self::getLoader());
        }
        return self::$_container;
    }
    public static function setGenerator(Generator $generator)
    {
        self::$_generator = $generator;
    }
    public static function getGenerator()
    {
        if (is_null(self::$_generator)) {
            self::$_generator = self::getDefaultGenerator();
        }
        return self::$_generator;
    }
    public static function getDefaultGenerator()
    {
        return new CachingGenerator(StringManipulationGenerator::withDefaultPasses());
    }
    public static function setLoader(Loader $loader)
    {
        self::$_loader = $loader;
    }
    public static function getLoader()
    {
        if (is_null(self::$_loader)) {
            self::$_loader = self::getDefaultLoader();
        }
        return self::$_loader;
    }
    public static function getDefaultLoader()
    {
        return new EvalLoader();
    }
    public static function setContainer(Mockery\Container $container)
    {
        return self::$_container = $container;
    }
    public static function resetContainer()
    {
        self::$_container = null;
    }
    public static function any()
    {
        return new \Mockery\Matcher\Any();
    }
    public static function andAnyOthers()
    {
        return new \Mockery\Matcher\AndAnyOtherArgs();
    }
    public static function andAnyOtherArgs()
    {
        return new \Mockery\Matcher\AndAnyOtherArgs();
    }
    public static function type($expected)
    {
        return new \Mockery\Matcher\Type($expected);
    }
    public static function ducktype(...$args)
    {
        return new \Mockery\Matcher\Ducktype($args);
    }
    public static function subset(array $part, $strict = true)
    {
        return new \Mockery\Matcher\Subset($part, $strict);
    }
    public static function contains(...$args)
    {
        return new \Mockery\Matcher\Contains($args);
    }
    public static function hasKey($key)
    {
        return new \Mockery\Matcher\HasKey($key);
    }
    public static function hasValue($val)
    {
        return new \Mockery\Matcher\HasValue($val);
    }
    public static function on($closure)
    {
        return new \Mockery\Matcher\Closure($closure);
    }
    public static function mustBe($expected)
    {
        return new \Mockery\Matcher\MustBe($expected);
    }
    public static function not($expected)
    {
        return new \Mockery\Matcher\Not($expected);
    }
    public static function anyOf(...$args)
    {
        return new \Mockery\Matcher\AnyOf($args);
    }
    public static function notAnyOf(...$args)
    {
        return new \Mockery\Matcher\NotAnyOf($args);
    }
    public static function pattern($expected)
    {
        return new \Mockery\Matcher\Pattern($expected);
    }
    public static function getConfiguration()
    {
        if (is_null(self::$_config)) {
            self::$_config = new \Mockery\Configuration();
        }
        return self::$_config;
    }
    public static function formatArgs($method, array $arguments = null)
    {
        if (is_null($arguments)) {
            return $method . '()';
        }
        $formattedArguments = array();
        foreach ($arguments as $argument) {
            $formattedArguments[] = self::formatArgument($argument);
        }
        return $method . '(' . implode(', ', $formattedArguments) . ')';
    }
    private static function formatArgument($argument, $depth = 0)
    {
        if ($argument instanceof MatcherAbstract) {
            return (string) $argument;
        }
        if (is_object($argument)) {
            return 'object(' . get_class($argument) . ')';
        }
        if (is_int($argument) || is_float($argument)) {
            return $argument;
        }
        if (is_array($argument)) {
            if ($depth === 1) {
                $argument = '[...]';
            } else {
                $sample = array();
                foreach ($argument as $key => $value) {
                    $key = is_int($key) ? $key : "'$key'";
                    $value = self::formatArgument($value, $depth + 1);
                    $sample[] = "$key => $value";
                }
                $argument = "[".implode(", ", $sample)."]";
            }
            return ((strlen($argument) > 1000) ? substr($argument, 0, 1000).'...]' : $argument);
        }
        if (is_bool($argument)) {
            return $argument ? 'true' : 'false';
        }
        if (is_resource($argument)) {
            return 'resource(...)';
        }
        if (is_null($argument)) {
            return 'NULL';
        }
        return "'".(string) $argument."'";
    }
    public static function formatObjects(array $objects = null)
    {
        static $formatting;
        if ($formatting) {
            return '[Recursion]';
        }
        if (is_null($objects)) {
            return '';
        }
        $objects = array_filter($objects, 'is_object');
        if (empty($objects)) {
            return '';
        }
        $formatting = true;
        $parts = array();
        foreach ($objects as $object) {
            $parts[get_class($object)] = self::objectToArray($object);
        }
        $formatting = false;
        return 'Objects: ( ' . var_export($parts, true) . ')';
    }
    private static function objectToArray($object, $nesting = 3)
    {
        if ($nesting == 0) {
            return array('...');
        }
        return array(
            'class' => get_class($object),
            'properties' => self::extractInstancePublicProperties($object, $nesting)
        );
    }
    private static function extractInstancePublicProperties($object, $nesting)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        $cleanedProperties = array();
        foreach ($properties as $publicProperty) {
            if (!$publicProperty->isStatic()) {
                $name = $publicProperty->getName();
                $cleanedProperties[$name] = self::cleanupNesting($object->$name, $nesting);
            }
        }
        return $cleanedProperties;
    }
    private static function cleanupNesting($argument, $nesting)
    {
        if (is_object($argument)) {
            $object = self::objectToArray($argument, $nesting - 1);
            $object['class'] = get_class($argument);
            return $object;
        }
        if (is_array($argument)) {
            return self::cleanupArray($argument, $nesting - 1);
        }
        return $argument;
    }
    private static function cleanupArray($argument, $nesting = 3)
    {
        if ($nesting == 0) {
            return '...';
        }
        foreach ($argument as $key => $value) {
            if (is_array($value)) {
                $argument[$key] = self::cleanupArray($value, $nesting - 1);
            } elseif (is_object($value)) {
                $argument[$key] = self::objectToArray($value, $nesting - 1);
            }
        }
        return $argument;
    }
    public static function parseShouldReturnArgs(\Mockery\MockInterface $mock, $args, $add)
    {
        $composite = new \Mockery\CompositeExpectation();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $k => $v) {
                    $expectation = self::buildDemeterChain($mock, $k, $add)->andReturn($v);
                    $composite->add($expectation);
                }
            } elseif (is_string($arg)) {
                $expectation = self::buildDemeterChain($mock, $arg, $add);
                $composite->add($expectation);
            }
        }
        return $composite;
    }
    protected static function buildDemeterChain(\Mockery\MockInterface $mock, $arg, $add)
    {
        $container = $mock->mockery_getContainer();
        $methodNames = explode('->', $arg);
        reset($methodNames);
        if (!\Mockery::getConfiguration()->mockingNonExistentMethodsAllowed()
            && !$mock->mockery_isAnonymous()
            && !in_array(current($methodNames), $mock->mockery_getMockableMethods())
        ) {
            throw new \Mockery\Exception(
                'Mockery\'s configuration currently forbids mocking the method '
                . current($methodNames) . ' as it does not exist on the class or object '
                . 'being mocked'
            );
        }
        $expectations = null;
        $nextExp = function ($method) use ($add) {
            return $add($method);
        };
        $parent = get_class($mock);
        while (true) {
            $method = array_shift($methodNames);
            $expectations = $mock->mockery_getExpectationsFor($method);
            if (is_null($expectations) || self::noMoreElementsInChain($methodNames)) {
                $expectations = $nextExp($method);
                if (self::noMoreElementsInChain($methodNames)) {
                    break;
                }
                $mock = self::getNewDemeterMock($container, $parent, $method, $expectations);
            } else {
                $demeterMockKey = $container->getKeyOfDemeterMockFor($method, $parent);
                if ($demeterMockKey) {
                    $mock = self::getExistingDemeterMock($container, $demeterMockKey);
                }
            }
            $parent .= '->' . $method;
            $nextExp = function ($n) use ($mock) {
                return $mock->shouldReceive($n);
            };
        }
        return $expectations;
    }
    private static function getNewDemeterMock(
        Mockery\Container $container,
        $parent,
        $method,
        Mockery\ExpectationInterface $exp
    ) {
        $newMockName = 'demeter_' . md5($parent) . '_' . $method;
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $parRef = null;
            $parRefMethod = null;
            $parRefMethodRetType = null;
            $parentMock = $exp->getMock();
            if ($parentMock !== null) {
                $parRef = new ReflectionObject($parentMock);
            }
            if ($parRef !== null && $parRef->hasMethod($method)) {
                $parRefMethod = $parRef->getMethod($method);
                $parRefMethodRetType = $parRefMethod->getReturnType();
                if ($parRefMethodRetType !== null) {
                    $mock = self::namedMock($newMockName, (string) $parRefMethodRetType);
                    $exp->andReturn($mock);
                    return $mock;
                }
            }
        }
        $mock = $container->mock($newMockName);
        $exp->andReturn($mock);
        return $mock;
    }
    private static function getExistingDemeterMock(
        Mockery\Container $container,
        $demeterMockKey
    ) {
        $mocks = $container->getMocks();
        $mock = $mocks[$demeterMockKey];
        return $mock;
    }
    private static function noMoreElementsInChain(array $methodNames)
    {
        return empty($methodNames);
    }
    public static function declareClass($fqn)
    {
        return static::declareType($fqn, "class");
    }
    public static function declareInterface($fqn)
    {
        return static::declareType($fqn, "interface");
    }
    private static function declareType($fqn, $type)
    {
        $targetCode = "<?php ";
        $shortName = $fqn;
        if (strpos($fqn, "\\")) {
            $parts = explode("\\", $fqn);
            $shortName = trim(array_pop($parts));
            $namespace = implode("\\", $parts);
            $targetCode.= "namespace $namespace;\n";
        }
        $targetCode.= "$type $shortName {} ";
        $tmpfname = tempnam(sys_get_temp_dir(), "Mockery");
        file_put_contents($tmpfname, $targetCode);
        require $tmpfname;
        \Mockery::registerFileForCleanUp($tmpfname);
    }
    public static function registerFileForCleanUp($fileName)
    {
        self::$_filesToCleanUp[] = $fileName;
    }
}
