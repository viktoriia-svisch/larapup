<?php
namespace PHPUnit\Framework;
use ArrayAccess;
use Countable;
use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\Constraint\ArraySubset;
use PHPUnit\Framework\Constraint\Attribute;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\ClassHasAttribute;
use PHPUnit\Framework\Constraint\ClassHasStaticAttribute;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\Count;
use PHPUnit\Framework\Constraint\DirectoryExists;
use PHPUnit\Framework\Constraint\FileExists;
use PHPUnit\Framework\Constraint\GreaterThan;
use PHPUnit\Framework\Constraint\IsAnything;
use PHPUnit\Framework\Constraint\IsEmpty;
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\Constraint\IsFalse;
use PHPUnit\Framework\Constraint\IsFinite;
use PHPUnit\Framework\Constraint\IsIdentical;
use PHPUnit\Framework\Constraint\IsInfinite;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Constraint\IsJson;
use PHPUnit\Framework\Constraint\IsNan;
use PHPUnit\Framework\Constraint\IsNull;
use PHPUnit\Framework\Constraint\IsReadable;
use PHPUnit\Framework\Constraint\IsTrue;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\Constraint\IsWritable;
use PHPUnit\Framework\Constraint\JsonMatches;
use PHPUnit\Framework\Constraint\LessThan;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\Constraint\LogicalXor;
use PHPUnit\Framework\Constraint\ObjectHasAttribute;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\Constraint\SameSize;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringMatchesFormatDescription;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Framework\Constraint\TraversableContainsOnly;
use PHPUnit\Util\InvalidArgumentHelper;
use PHPUnit\Util\Type;
use PHPUnit\Util\Xml;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use Traversable;
abstract class Assert
{
    private static $count = 0;
    public static function assertArrayHasKey($key, $array, string $message = ''): void
    {
        if (!(\is_int($key) || \is_string($key))) {
            throw InvalidArgumentHelper::factory(
                1,
                'integer or string'
            );
        }
        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }
        $constraint = new ArrayHasKey($key);
        static::assertThat($array, $constraint, $message);
    }
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        if (!(\is_array($subset) || $subset instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                1,
                'array or ArrayAccess'
            );
        }
        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }
        $constraint = new ArraySubset($subset, $checkForObjectIdentity);
        static::assertThat($array, $constraint, $message);
    }
    public static function assertArrayNotHasKey($key, $array, string $message = ''): void
    {
        if (!(\is_int($key) || \is_string($key))) {
            throw InvalidArgumentHelper::factory(
                1,
                'integer or string'
            );
        }
        if (!(\is_array($array) || $array instanceof ArrayAccess)) {
            throw InvalidArgumentHelper::factory(
                2,
                'array or ArrayAccess'
            );
        }
        $constraint = new LogicalNot(
            new ArrayHasKey($key)
        );
        static::assertThat($array, $constraint, $message);
    }
    public static function assertContains($needle, $haystack, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        if (\is_array($haystack) ||
            (\is_object($haystack) && $haystack instanceof Traversable)) {
            $constraint = new TraversableContains(
                $needle,
                $checkForObjectIdentity,
                $checkForNonObjectIdentity
            );
        } elseif (\is_string($haystack)) {
            if (!\is_string($needle)) {
                throw InvalidArgumentHelper::factory(
                    1,
                    'string'
                );
            }
            $constraint = new StringContains(
                $needle,
                $ignoreCase
            );
        } else {
            throw InvalidArgumentHelper::factory(
                2,
                'array, traversable or string'
            );
        }
        static::assertThat($haystack, $constraint, $message);
    }
    public static function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        static::assertContains(
            $needle,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
    }
    public static function assertNotContains($needle, $haystack, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        if (\is_array($haystack) ||
            (\is_object($haystack) && $haystack instanceof Traversable)) {
            $constraint = new LogicalNot(
                new TraversableContains(
                    $needle,
                    $checkForObjectIdentity,
                    $checkForNonObjectIdentity
                )
            );
        } elseif (\is_string($haystack)) {
            if (!\is_string($needle)) {
                throw InvalidArgumentHelper::factory(
                    1,
                    'string'
                );
            }
            $constraint = new LogicalNot(
                new StringContains(
                    $needle,
                    $ignoreCase
                )
            );
        } else {
            throw InvalidArgumentHelper::factory(
                2,
                'array, traversable or string'
            );
        }
        static::assertThat($haystack, $constraint, $message);
    }
    public static function assertAttributeNotContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
    {
        static::assertNotContains(
            $needle,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message,
            $ignoreCase,
            $checkForObjectIdentity,
            $checkForNonObjectIdentity
        );
    }
    public static function assertContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
    {
        if ($isNativeType === null) {
            $isNativeType = Type::isType($type);
        }
        static::assertThat(
            $haystack,
            new TraversableContainsOnly(
                $type,
                $isNativeType
            ),
            $message
        );
    }
    public static function assertContainsOnlyInstancesOf(string $className, iterable $haystack, string $message = ''): void
    {
        static::assertThat(
            $haystack,
            new TraversableContainsOnly(
                $className,
                false
            ),
            $message
        );
    }
    public static function assertAttributeContainsOnly(string $type, string $haystackAttributeName, $haystackClassOrObject, ?bool $isNativeType = null, string $message = ''): void
    {
        static::assertContainsOnly(
            $type,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $isNativeType,
            $message
        );
    }
    public static function assertNotContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
    {
        if ($isNativeType === null) {
            $isNativeType = Type::isType($type);
        }
        static::assertThat(
            $haystack,
            new LogicalNot(
                new TraversableContainsOnly(
                    $type,
                    $isNativeType
                )
            ),
            $message
        );
    }
    public static function assertAttributeNotContainsOnly(string $type, string $haystackAttributeName, $haystackClassOrObject, ?bool $isNativeType = null, string $message = ''): void
    {
        static::assertNotContainsOnly(
            $type,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $isNativeType,
            $message
        );
    }
    public static function assertCount(int $expectedCount, $haystack, string $message = ''): void
    {
        if (!$haystack instanceof Countable && !\is_iterable($haystack)) {
            throw InvalidArgumentHelper::factory(2, 'countable or iterable');
        }
        static::assertThat(
            $haystack,
            new Count($expectedCount),
            $message
        );
    }
    public static function assertAttributeCount(int $expectedCount, string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
    {
        static::assertCount(
            $expectedCount,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }
    public static function assertNotCount(int $expectedCount, $haystack, string $message = ''): void
    {
        if (!$haystack instanceof Countable && !\is_iterable($haystack)) {
            throw InvalidArgumentHelper::factory(2, 'countable or iterable');
        }
        $constraint = new LogicalNot(
            new Count($expectedCount)
        );
        static::assertThat($haystack, $constraint, $message);
    }
    public static function assertAttributeNotCount(int $expectedCount, string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
    {
        static::assertNotCount(
            $expectedCount,
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }
    public static function assertEquals($expected, $actual, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        $constraint = new IsEqual(
            $expected,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertEqualsCanonicalizing($expected, $actual, string $message = ''): void
    {
        $constraint = new IsEqual(
            $expected,
            0.0,
            10,
            true,
            false
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertEqualsIgnoringCase($expected, $actual, string $message = ''): void
    {
        $constraint = new IsEqual(
            $expected,
            0.0,
            10,
            false,
            true
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertEqualsWithDelta($expected, $actual, float $delta, string $message = ''): void
    {
        $constraint = new IsEqual(
            $expected,
            $delta
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertAttributeEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertEquals(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }
    public static function assertNotEquals($expected, $actual, string $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false): void
    {
        $constraint = new LogicalNot(
            new IsEqual(
                $expected,
                $delta,
                $maxDepth,
                $canonicalize,
                $ignoreCase
            )
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertNotEqualsCanonicalizing($expected, $actual, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new IsEqual(
                $expected,
                0.0,
                10,
                true,
                false
            )
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertNotEqualsIgnoringCase($expected, $actual, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new IsEqual(
                $expected,
                0.0,
                10,
                false,
                true
            )
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertNotEqualsWithDelta($expected, $actual, float $delta, string $message = ''): void
    {
        $constraint = new LogicalNot(
            new IsEqual(
                $expected,
                $delta
            )
        );
        static::assertThat($actual, $constraint, $message);
    }
    public static function assertAttributeNotEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertNotEquals(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message,
            $delta,
            $maxDepth,
            $canonicalize,
            $ignoreCase
        );
    }
    public static function assertEmpty($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isEmpty(), $message);
    }
    public static function assertAttributeEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
    {
        static::assertEmpty(
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }
    public static function assertNotEmpty($actual, string $message = ''): void
    {
        static::assertThat($actual, static::logicalNot(static::isEmpty()), $message);
    }
    public static function assertAttributeNotEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
    {
        static::assertNotEmpty(
            static::readAttribute($haystackClassOrObject, $haystackAttributeName),
            $message
        );
    }
    public static function assertGreaterThan($expected, $actual, string $message = ''): void
    {
        static::assertThat($actual, static::greaterThan($expected), $message);
    }
    public static function assertAttributeGreaterThan($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        static::assertGreaterThan(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }
    public static function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            static::greaterThanOrEqual($expected),
            $message
        );
    }
    public static function assertAttributeGreaterThanOrEqual($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        static::assertGreaterThanOrEqual(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }
    public static function assertLessThan($expected, $actual, string $message = ''): void
    {
        static::assertThat($actual, static::lessThan($expected), $message);
    }
    public static function assertAttributeLessThan($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        static::assertLessThan(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }
    public static function assertLessThanOrEqual($expected, $actual, string $message = ''): void
    {
        static::assertThat($actual, static::lessThanOrEqual($expected), $message);
    }
    public static function assertAttributeLessThanOrEqual($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        static::assertLessThanOrEqual(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }
    public static function assertFileEquals(string $expected, string $actual, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);
        static::assertEquals(
            \file_get_contents($expected),
            \file_get_contents($actual),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }
    public static function assertFileNotEquals(string $expected, string $actual, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertFileExists($expected, $message);
        static::assertFileExists($actual, $message);
        static::assertNotEquals(
            \file_get_contents($expected),
            \file_get_contents($actual),
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }
    public static function assertStringEqualsFile(string $expectedFile, string $actualString, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertFileExists($expectedFile, $message);
        static::assertEquals(
            \file_get_contents($expectedFile),
            $actualString,
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }
    public static function assertStringNotEqualsFile(string $expectedFile, string $actualString, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
    {
        static::assertFileExists($expectedFile, $message);
        static::assertNotEquals(
            \file_get_contents($expectedFile),
            $actualString,
            $message,
            0,
            10,
            $canonicalize,
            $ignoreCase
        );
    }
    public static function assertIsReadable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new IsReadable, $message);
    }
    public static function assertNotIsReadable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new LogicalNot(new IsReadable), $message);
    }
    public static function assertIsWritable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new IsWritable, $message);
    }
    public static function assertNotIsWritable(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new LogicalNot(new IsWritable), $message);
    }
    public static function assertDirectoryExists(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new DirectoryExists, $message);
    }
    public static function assertDirectoryNotExists(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new LogicalNot(new DirectoryExists), $message);
    }
    public static function assertDirectoryIsReadable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsReadable($directory, $message);
    }
    public static function assertDirectoryNotIsReadable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertNotIsReadable($directory, $message);
    }
    public static function assertDirectoryIsWritable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertIsWritable($directory, $message);
    }
    public static function assertDirectoryNotIsWritable(string $directory, string $message = ''): void
    {
        self::assertDirectoryExists($directory, $message);
        self::assertNotIsWritable($directory, $message);
    }
    public static function assertFileExists(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new FileExists, $message);
    }
    public static function assertFileNotExists(string $filename, string $message = ''): void
    {
        static::assertThat($filename, new LogicalNot(new FileExists), $message);
    }
    public static function assertFileIsReadable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertIsReadable($file, $message);
    }
    public static function assertFileNotIsReadable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertNotIsReadable($file, $message);
    }
    public static function assertFileIsWritable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertIsWritable($file, $message);
    }
    public static function assertFileNotIsWritable(string $file, string $message = ''): void
    {
        self::assertFileExists($file, $message);
        self::assertNotIsWritable($file, $message);
    }
    public static function assertTrue($condition, string $message = ''): void
    {
        static::assertThat($condition, static::isTrue(), $message);
    }
    public static function assertNotTrue($condition, string $message = ''): void
    {
        static::assertThat($condition, static::logicalNot(static::isTrue()), $message);
    }
    public static function assertFalse($condition, string $message = ''): void
    {
        static::assertThat($condition, static::isFalse(), $message);
    }
    public static function assertNotFalse($condition, string $message = ''): void
    {
        static::assertThat($condition, static::logicalNot(static::isFalse()), $message);
    }
    public static function assertNull($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isNull(), $message);
    }
    public static function assertNotNull($actual, string $message = ''): void
    {
        static::assertThat($actual, static::logicalNot(static::isNull()), $message);
    }
    public static function assertFinite($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isFinite(), $message);
    }
    public static function assertInfinite($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isInfinite(), $message);
    }
    public static function assertNan($actual, string $message = ''): void
    {
        static::assertThat($actual, static::isNan(), $message);
    }
    public static function assertClassHasAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }
        if (!\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }
        static::assertThat($className, new ClassHasAttribute($attributeName), $message);
    }
    public static function assertClassNotHasAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }
        if (!\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }
        static::assertThat(
            $className,
            new LogicalNot(
                new ClassHasAttribute($attributeName)
            ),
            $message
        );
    }
    public static function assertClassHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }
        if (!\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }
        static::assertThat(
            $className,
            new ClassHasStaticAttribute($attributeName),
            $message
        );
    }
    public static function assertClassNotHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
    {
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }
        if (!\class_exists($className)) {
            throw InvalidArgumentHelper::factory(2, 'class name', $className);
        }
        static::assertThat(
            $className,
            new LogicalNot(
                new ClassHasStaticAttribute($attributeName)
            ),
            $message
        );
    }
    public static function assertObjectHasAttribute(string $attributeName, $object, string $message = ''): void
    {
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }
        if (!\is_object($object)) {
            throw InvalidArgumentHelper::factory(2, 'object');
        }
        static::assertThat(
            $object,
            new ObjectHasAttribute($attributeName),
            $message
        );
    }
    public static function assertObjectNotHasAttribute(string $attributeName, $object, string $message = ''): void
    {
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(1, 'valid attribute name');
        }
        if (!\is_object($object)) {
            throw InvalidArgumentHelper::factory(2, 'object');
        }
        static::assertThat(
            $object,
            new LogicalNot(
                new ObjectHasAttribute($attributeName)
            ),
            $message
        );
    }
    public static function assertSame($expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsIdentical($expected),
            $message
        );
    }
    public static function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        static::assertSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }
    public static function assertNotSame($expected, $actual, string $message = ''): void
    {
        if (\is_bool($expected) && \is_bool($actual)) {
            static::assertNotEquals($expected, $actual, $message);
        }
        static::assertThat(
            $actual,
            new LogicalNot(
                new IsIdentical($expected)
            ),
            $message
        );
    }
    public static function assertAttributeNotSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
    {
        static::assertNotSame(
            $expected,
            static::readAttribute($actualClassOrObject, $actualAttributeName),
            $message
        );
    }
    public static function assertInstanceOf(string $expected, $actual, string $message = ''): void
    {
        if (!\class_exists($expected) && !\interface_exists($expected)) {
            throw InvalidArgumentHelper::factory(1, 'class or interface name');
        }
        static::assertThat(
            $actual,
            new IsInstanceOf($expected),
            $message
        );
    }
    public static function assertAttributeInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
    {
        static::assertInstanceOf(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }
    public static function assertNotInstanceOf(string $expected, $actual, string $message = ''): void
    {
        if (!\class_exists($expected) && !\interface_exists($expected)) {
            throw InvalidArgumentHelper::factory(1, 'class or interface name');
        }
        static::assertThat(
            $actual,
            new LogicalNot(
                new IsInstanceOf($expected)
            ),
            $message
        );
    }
    public static function assertAttributeNotInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
    {
        static::assertNotInstanceOf(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }
    public static function assertInternalType(string $expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType($expected),
            $message
        );
    }
    public static function assertAttributeInternalType(string $expected, string $attributeName, $classOrObject, string $message = ''): void
    {
        static::assertInternalType(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }
    public static function assertIsArray($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_ARRAY),
            $message
        );
    }
    public static function assertIsBool($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_BOOL),
            $message
        );
    }
    public static function assertIsFloat($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_FLOAT),
            $message
        );
    }
    public static function assertIsInt($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_INT),
            $message
        );
    }
    public static function assertIsNumeric($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_NUMERIC),
            $message
        );
    }
    public static function assertIsObject($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_OBJECT),
            $message
        );
    }
    public static function assertIsResource($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_RESOURCE),
            $message
        );
    }
    public static function assertIsString($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_STRING),
            $message
        );
    }
    public static function assertIsScalar($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_SCALAR),
            $message
        );
    }
    public static function assertIsCallable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_CALLABLE),
            $message
        );
    }
    public static function assertIsIterable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new IsType(IsType::TYPE_ITERABLE),
            $message
        );
    }
    public static function assertNotInternalType(string $expected, $actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(
                new IsType($expected)
            ),
            $message
        );
    }
    public static function assertIsNotArray($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_ARRAY)),
            $message
        );
    }
    public static function assertIsNotBool($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_BOOL)),
            $message
        );
    }
    public static function assertIsNotFloat($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_FLOAT)),
            $message
        );
    }
    public static function assertIsNotInt($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_INT)),
            $message
        );
    }
    public static function assertIsNotNumeric($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_NUMERIC)),
            $message
        );
    }
    public static function assertIsNotObject($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_OBJECT)),
            $message
        );
    }
    public static function assertIsNotResource($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_RESOURCE)),
            $message
        );
    }
    public static function assertIsNotString($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_STRING)),
            $message
        );
    }
    public static function assertIsNotScalar($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_SCALAR)),
            $message
        );
    }
    public static function assertIsNotCallable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_CALLABLE)),
            $message
        );
    }
    public static function assertIsNotIterable($actual, string $message = ''): void
    {
        static::assertThat(
            $actual,
            new LogicalNot(new IsType(IsType::TYPE_ITERABLE)),
            $message
        );
    }
    public static function assertAttributeNotInternalType(string $expected, string $attributeName, $classOrObject, string $message = ''): void
    {
        static::assertNotInternalType(
            $expected,
            static::readAttribute($classOrObject, $attributeName),
            $message
        );
    }
    public static function assertRegExp(string $pattern, string $string, string $message = ''): void
    {
        static::assertThat($string, new RegularExpression($pattern), $message);
    }
    public static function assertNotRegExp(string $pattern, string $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new RegularExpression($pattern)
            ),
            $message
        );
    }
    public static function assertSameSize($expected, $actual, string $message = ''): void
    {
        if (!$expected instanceof Countable && !\is_iterable($expected)) {
            throw InvalidArgumentHelper::factory(1, 'countable or iterable');
        }
        if (!$actual instanceof Countable && !\is_iterable($actual)) {
            throw InvalidArgumentHelper::factory(2, 'countable or iterable');
        }
        static::assertThat(
            $actual,
            new SameSize($expected),
            $message
        );
    }
    public static function assertNotSameSize($expected, $actual, string $message = ''): void
    {
        if (!$expected instanceof Countable && !\is_iterable($expected)) {
            throw InvalidArgumentHelper::factory(1, 'countable or iterable');
        }
        if (!$actual instanceof Countable && !\is_iterable($actual)) {
            throw InvalidArgumentHelper::factory(2, 'countable or iterable');
        }
        static::assertThat(
            $actual,
            new LogicalNot(
                new SameSize($expected)
            ),
            $message
        );
    }
    public static function assertStringMatchesFormat(string $format, string $string, string $message = ''): void
    {
        static::assertThat($string, new StringMatchesFormatDescription($format), $message);
    }
    public static function assertStringNotMatchesFormat(string $format, string $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new StringMatchesFormatDescription($format)
            ),
            $message
        );
    }
    public static function assertStringMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
    {
        static::assertFileExists($formatFile, $message);
        static::assertThat(
            $string,
            new StringMatchesFormatDescription(
                \file_get_contents($formatFile)
            ),
            $message
        );
    }
    public static function assertStringNotMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
    {
        static::assertFileExists($formatFile, $message);
        static::assertThat(
            $string,
            new LogicalNot(
                new StringMatchesFormatDescription(
                    \file_get_contents($formatFile)
                )
            ),
            $message
        );
    }
    public static function assertStringStartsWith(string $prefix, string $string, string $message = ''): void
    {
        static::assertThat($string, new StringStartsWith($prefix), $message);
    }
    public static function assertStringStartsNotWith($prefix, $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new StringStartsWith($prefix)
            ),
            $message
        );
    }
    public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new StringContains($needle, false);
        static::assertThat($haystack, $constraint, $message);
    }
    public static function assertStringContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new StringContains($needle, true);
        static::assertThat($haystack, $constraint, $message);
    }
    public static function assertStringNotContainsString(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(new StringContains($needle));
        static::assertThat($haystack, $constraint, $message);
    }
    public static function assertStringNotContainsStringIgnoringCase(string $needle, string $haystack, string $message = ''): void
    {
        $constraint = new LogicalNot(new StringContains($needle, true));
        static::assertThat($haystack, $constraint, $message);
    }
    public static function assertStringEndsWith(string $suffix, string $string, string $message = ''): void
    {
        static::assertThat($string, new StringEndsWith($suffix), $message);
    }
    public static function assertStringEndsNotWith(string $suffix, string $string, string $message = ''): void
    {
        static::assertThat(
            $string,
            new LogicalNot(
                new StringEndsWith($suffix)
            ),
            $message
        );
    }
    public static function assertXmlFileEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::loadFile($actualFile);
        static::assertEquals($expected, $actual, $message);
    }
    public static function assertXmlFileNotEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::loadFile($actualFile);
        static::assertNotEquals($expected, $actual, $message);
    }
    public static function assertXmlStringEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::load($actualXml);
        static::assertEquals($expected, $actual, $message);
    }
    public static function assertXmlStringNotEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
    {
        $expected = Xml::loadFile($expectedFile);
        $actual   = Xml::load($actualXml);
        static::assertNotEquals($expected, $actual, $message);
    }
    public static function assertXmlStringEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
    {
        $expected = Xml::load($expectedXml);
        $actual   = Xml::load($actualXml);
        static::assertEquals($expected, $actual, $message);
    }
    public static function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
    {
        $expected = Xml::load($expectedXml);
        $actual   = Xml::load($actualXml);
        static::assertNotEquals($expected, $actual, $message);
    }
    public static function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, bool $checkAttributes = false, string $message = ''): void
    {
        $expectedElement = Xml::import($expectedElement);
        $actualElement   = Xml::import($actualElement);
        static::assertSame(
            $expectedElement->tagName,
            $actualElement->tagName,
            $message
        );
        if ($checkAttributes) {
            static::assertSame(
                $expectedElement->attributes->length,
                $actualElement->attributes->length,
                \sprintf(
                    '%s%sNumber of attributes on node "%s" does not match',
                    $message,
                    !empty($message) ? "\n" : '',
                    $expectedElement->tagName
                )
            );
            for ($i = 0; $i < $expectedElement->attributes->length; $i++) {
                $expectedAttribute = $expectedElement->attributes->item($i);
                $actualAttribute = $actualElement->attributes->getNamedItem(
                    $expectedAttribute->name
                );
                if (!$actualAttribute) {
                    static::fail(
                        \sprintf(
                            '%s%sCould not find attribute "%s" on node "%s"',
                            $message,
                            !empty($message) ? "\n" : '',
                            $expectedAttribute->name,
                            $expectedElement->tagName
                        )
                    );
                }
            }
        }
        Xml::removeCharacterDataNodes($expectedElement);
        Xml::removeCharacterDataNodes($actualElement);
        static::assertSame(
            $expectedElement->childNodes->length,
            $actualElement->childNodes->length,
            \sprintf(
                '%s%sNumber of child nodes of "%s" differs',
                $message,
                !empty($message) ? "\n" : '',
                $expectedElement->tagName
            )
        );
        for ($i = 0; $i < $expectedElement->childNodes->length; $i++) {
            static::assertEqualXMLStructure(
                $expectedElement->childNodes->item($i),
                $actualElement->childNodes->item($i),
                $checkAttributes,
                $message
            );
        }
    }
    public static function assertThat($value, Constraint $constraint, string $message = ''): void
    {
        self::$count += \count($constraint);
        $constraint->evaluate($value, $message);
    }
    public static function assertJson(string $actualJson, string $message = ''): void
    {
        static::assertThat($actualJson, static::isJson(), $message);
    }
    public static function assertJsonStringEqualsJsonString(string $expectedJson, string $actualJson, string $message = ''): void
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);
        static::assertThat($actualJson, new JsonMatches($expectedJson), $message);
    }
    public static function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, string $message = ''): void
    {
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);
        static::assertThat(
            $actualJson,
            new LogicalNot(
                new JsonMatches($expectedJson)
            ),
            $message
        );
    }
    public static function assertJsonStringEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = \file_get_contents($expectedFile);
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);
        static::assertThat($actualJson, new JsonMatches($expectedJson), $message);
    }
    public static function assertJsonStringNotEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        $expectedJson = \file_get_contents($expectedFile);
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);
        static::assertThat(
            $actualJson,
            new LogicalNot(
                new JsonMatches($expectedJson)
            ),
            $message
        );
    }
    public static function assertJsonFileEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);
        $actualJson   = \file_get_contents($actualFile);
        $expectedJson = \file_get_contents($expectedFile);
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);
        $constraintExpected = new JsonMatches(
            $expectedJson
        );
        $constraintActual = new JsonMatches($actualJson);
        static::assertThat($expectedJson, $constraintActual, $message);
        static::assertThat($actualJson, $constraintExpected, $message);
    }
    public static function assertJsonFileNotEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
    {
        static::assertFileExists($expectedFile, $message);
        static::assertFileExists($actualFile, $message);
        $actualJson   = \file_get_contents($actualFile);
        $expectedJson = \file_get_contents($expectedFile);
        static::assertJson($expectedJson, $message);
        static::assertJson($actualJson, $message);
        $constraintExpected = new JsonMatches(
            $expectedJson
        );
        $constraintActual = new JsonMatches($actualJson);
        static::assertThat($expectedJson, new LogicalNot($constraintActual), $message);
        static::assertThat($actualJson, new LogicalNot($constraintExpected), $message);
    }
    public static function logicalAnd(): LogicalAnd
    {
        $constraints = \func_get_args();
        $constraint = new LogicalAnd;
        $constraint->setConstraints($constraints);
        return $constraint;
    }
    public static function logicalOr(): LogicalOr
    {
        $constraints = \func_get_args();
        $constraint = new LogicalOr;
        $constraint->setConstraints($constraints);
        return $constraint;
    }
    public static function logicalNot(Constraint $constraint): LogicalNot
    {
        return new LogicalNot($constraint);
    }
    public static function logicalXor(): LogicalXor
    {
        $constraints = \func_get_args();
        $constraint = new LogicalXor;
        $constraint->setConstraints($constraints);
        return $constraint;
    }
    public static function anything(): IsAnything
    {
        return new IsAnything;
    }
    public static function isTrue(): IsTrue
    {
        return new IsTrue;
    }
    public static function callback(callable $callback): Callback
    {
        return new Callback($callback);
    }
    public static function isFalse(): IsFalse
    {
        return new IsFalse;
    }
    public static function isJson(): IsJson
    {
        return new IsJson;
    }
    public static function isNull(): IsNull
    {
        return new IsNull;
    }
    public static function isFinite(): IsFinite
    {
        return new IsFinite;
    }
    public static function isInfinite(): IsInfinite
    {
        return new IsInfinite;
    }
    public static function isNan(): IsNan
    {
        return new IsNan;
    }
    public static function attribute(Constraint $constraint, string $attributeName): Attribute
    {
        return new Attribute($constraint, $attributeName);
    }
    public static function contains($value, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): TraversableContains
    {
        return new TraversableContains($value, $checkForObjectIdentity, $checkForNonObjectIdentity);
    }
    public static function containsOnly(string $type): TraversableContainsOnly
    {
        return new TraversableContainsOnly($type);
    }
    public static function containsOnlyInstancesOf(string $className): TraversableContainsOnly
    {
        return new TraversableContainsOnly($className, false);
    }
    public static function arrayHasKey($key): ArrayHasKey
    {
        return new ArrayHasKey($key);
    }
    public static function equalTo($value, float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): IsEqual
    {
        return new IsEqual($value, $delta, $maxDepth, $canonicalize, $ignoreCase);
    }
    public static function attributeEqualTo(string $attributeName, $value, float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): Attribute
    {
        return static::attribute(
            static::equalTo(
                $value,
                $delta,
                $maxDepth,
                $canonicalize,
                $ignoreCase
            ),
            $attributeName
        );
    }
    public static function isEmpty(): IsEmpty
    {
        return new IsEmpty;
    }
    public static function isWritable(): IsWritable
    {
        return new IsWritable;
    }
    public static function isReadable(): IsReadable
    {
        return new IsReadable;
    }
    public static function directoryExists(): DirectoryExists
    {
        return new DirectoryExists;
    }
    public static function fileExists(): FileExists
    {
        return new FileExists;
    }
    public static function greaterThan($value): GreaterThan
    {
        return new GreaterThan($value);
    }
    public static function greaterThanOrEqual($value): LogicalOr
    {
        return static::logicalOr(
            new IsEqual($value),
            new GreaterThan($value)
        );
    }
    public static function classHasAttribute(string $attributeName): ClassHasAttribute
    {
        return new ClassHasAttribute($attributeName);
    }
    public static function classHasStaticAttribute(string $attributeName): ClassHasStaticAttribute
    {
        return new ClassHasStaticAttribute($attributeName);
    }
    public static function objectHasAttribute($attributeName): ObjectHasAttribute
    {
        return new ObjectHasAttribute($attributeName);
    }
    public static function identicalTo($value): IsIdentical
    {
        return new IsIdentical($value);
    }
    public static function isInstanceOf(string $className): IsInstanceOf
    {
        return new IsInstanceOf($className);
    }
    public static function isType(string $type): IsType
    {
        return new IsType($type);
    }
    public static function lessThan($value): LessThan
    {
        return new LessThan($value);
    }
    public static function lessThanOrEqual($value): LogicalOr
    {
        return static::logicalOr(
            new IsEqual($value),
            new LessThan($value)
        );
    }
    public static function matchesRegularExpression(string $pattern): RegularExpression
    {
        return new RegularExpression($pattern);
    }
    public static function matches(string $string): StringMatchesFormatDescription
    {
        return new StringMatchesFormatDescription($string);
    }
    public static function stringStartsWith($prefix): StringStartsWith
    {
        return new StringStartsWith($prefix);
    }
    public static function stringContains(string $string, bool $case = true): StringContains
    {
        return new StringContains($string, $case);
    }
    public static function stringEndsWith(string $suffix): StringEndsWith
    {
        return new StringEndsWith($suffix);
    }
    public static function countOf(int $count): Count
    {
        return new Count($count);
    }
    public static function fail(string $message = ''): void
    {
        self::$count++;
        throw new AssertionFailedError($message);
    }
    public static function readAttribute($classOrObject, string $attributeName)
    {
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'valid attribute name');
        }
        if (\is_string($classOrObject)) {
            if (!\class_exists($classOrObject)) {
                throw InvalidArgumentHelper::factory(
                    1,
                    'class name'
                );
            }
            return static::getStaticAttribute(
                $classOrObject,
                $attributeName
            );
        }
        if (\is_object($classOrObject)) {
            return static::getObjectAttribute(
                $classOrObject,
                $attributeName
            );
        }
        throw InvalidArgumentHelper::factory(
            1,
            'class name or object'
        );
    }
    public static function getStaticAttribute(string $className, string $attributeName)
    {
        if (!\class_exists($className)) {
            throw InvalidArgumentHelper::factory(1, 'class name');
        }
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'valid attribute name');
        }
        $class = new ReflectionClass($className);
        while ($class) {
            $attributes = $class->getStaticProperties();
            if (\array_key_exists($attributeName, $attributes)) {
                return $attributes[$attributeName];
            }
            $class = $class->getParentClass();
        }
        throw new Exception(
            \sprintf(
                'Attribute "%s" not found in class.',
                $attributeName
            )
        );
    }
    public static function getObjectAttribute($object, string $attributeName)
    {
        if (!\is_object($object)) {
            throw InvalidArgumentHelper::factory(1, 'object');
        }
        if (!self::isValidAttributeName($attributeName)) {
            throw InvalidArgumentHelper::factory(2, 'valid attribute name');
        }
        try {
            $reflector = new ReflectionObject($object);
            do {
                try {
                    $attribute = $reflector->getProperty($attributeName);
                    if (!$attribute || $attribute->isPublic()) {
                        return $object->$attributeName;
                    }
                    $attribute->setAccessible(true);
                    $value = $attribute->getValue($object);
                    $attribute->setAccessible(false);
                    return $value;
                } catch (ReflectionException $e) {
                }
            } while ($reflector = $reflector->getParentClass());
        } catch (ReflectionException $e) {
        }
        throw new Exception(
            \sprintf(
                'Attribute "%s" not found in object.',
                $attributeName
            )
        );
    }
    public static function markTestIncomplete(string $message = ''): void
    {
        throw new IncompleteTestError($message);
    }
    public static function markTestSkipped(string $message = ''): void
    {
        throw new SkippedTestError($message);
    }
    public static function getCount(): int
    {
        return self::$count;
    }
    public static function resetCount(): void
    {
        self::$count = 0;
    }
    private static function isValidAttributeName(string $attributeName): bool
    {
        return \preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $attributeName);
    }
    private static function createWarning(string $warning): void
    {
        foreach (\debug_backtrace() as $step) {
            if (isset($step['object']) && $step['object'] instanceof TestCase) {
                $step['object']->addWarning($warning);
                break;
            }
        }
    }
}
