<?php
namespace PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\SelfDescribing;
use ReflectionObject;
use SebastianBergmann\Exporter\Exporter;
class StaticInvocation implements Invocation, SelfDescribing
{
    private static $uncloneableExtensions = [
        'mysqli'    => true,
        'SQLite'    => true,
        'sqlite3'   => true,
        'tidy'      => true,
        'xmlwriter' => true,
        'xsl'       => true,
    ];
    private static $uncloneableClasses = [
        'Closure',
        'COMPersistHelper',
        'IteratorIterator',
        'RecursiveIteratorIterator',
        'SplFileObject',
        'PDORow',
        'ZipArchive',
    ];
    private $className;
    private $methodName;
    private $parameters;
    private $returnType;
    private $isReturnTypeNullable = false;
    public function __construct($className, $methodName, array $parameters, $returnType, $cloneObjects = false)
    {
        $this->className  = $className;
        $this->methodName = $methodName;
        $this->parameters = $parameters;
        if (\strtolower($methodName) === '__tostring') {
            $returnType = 'string';
        }
        if (\strpos($returnType, '?') === 0) {
            $returnType                 = \substr($returnType, 1);
            $this->isReturnTypeNullable = true;
        }
        $this->returnType = $returnType;
        if (!$cloneObjects) {
            return;
        }
        foreach ($this->parameters as $key => $value) {
            if (\is_object($value)) {
                $this->parameters[$key] = $this->cloneObject($value);
            }
        }
    }
    public function getClassName(): string
    {
        return $this->className;
    }
    public function getMethodName(): string
    {
        return $this->methodName;
    }
    public function getParameters(): array
    {
        return $this->parameters;
    }
    public function getReturnType(): string
    {
        return $this->returnType;
    }
    public function isReturnTypeNullable(): bool
    {
        return $this->isReturnTypeNullable;
    }
    public function generateReturnValue()
    {
        if ($this->isReturnTypeNullable) {
            return;
        }
        switch (\strtolower($this->returnType)) {
            case '':
            case 'void':
                return;
            case 'string':
                return '';
            case 'float':
                return 0.0;
            case 'int':
                return 0;
            case 'bool':
                return false;
            case 'array':
                return [];
            case 'object':
                return new \stdClass;
            case 'callable':
            case 'closure':
                return function (): void {
                };
            case 'traversable':
            case 'generator':
            case 'iterable':
                $generator = function () {
                    yield;
                };
                return $generator();
            default:
                $generator = new Generator;
                return $generator->getMock($this->returnType, [], [], '', false);
        }
    }
    public function toString(): string
    {
        $exporter = new Exporter;
        return \sprintf(
            '%s::%s(%s)%s',
            $this->className,
            $this->methodName,
            \implode(
                ', ',
                \array_map(
                    [$exporter, 'shortenedExport'],
                    $this->parameters
                )
            ),
            $this->returnType ? \sprintf(': %s', $this->returnType) : ''
        );
    }
    private function cloneObject($original)
    {
        $cloneable = null;
        $object    = new ReflectionObject($original);
        if ($object->isInternal() &&
            isset(self::$uncloneableExtensions[$object->getExtensionName()])) {
            $cloneable = false;
        }
        if ($cloneable === null) {
            foreach (self::$uncloneableClasses as $class) {
                if ($original instanceof $class) {
                    $cloneable = false;
                    break;
                }
            }
        }
        if ($cloneable === null) {
            $cloneable = $object->isCloneable();
        }
        if ($cloneable === null && $object->hasMethod('__clone')) {
            $method    = $object->getMethod('__clone');
            $cloneable = $method->isPublic();
        }
        if ($cloneable === null) {
            $cloneable = true;
        }
        if ($cloneable) {
            try {
                return clone $original;
            } catch (\Exception $e) {
                return $original;
            }
        } else {
            return $original;
        }
    }
}
