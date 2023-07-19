<?php
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Constraint\ArrayHasKey;
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
use PHPUnit\Framework\Constraint\LessThan;
use PHPUnit\Framework\Constraint\LogicalAnd;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\Constraint\LogicalOr;
use PHPUnit\Framework\Constraint\LogicalXor;
use PHPUnit\Framework\Constraint\ObjectHasAttribute;
use PHPUnit\Framework\Constraint\RegularExpression;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\Constraint\StringEndsWith;
use PHPUnit\Framework\Constraint\StringMatchesFormatDescription;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\Constraint\TraversableContains;
use PHPUnit\Framework\Constraint\TraversableContainsOnly;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount as AnyInvokedCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtIndex as InvokedAtIndexMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastCount as InvokedAtLeastCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastOnce as InvokedAtLeastOnceMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtMostCount as InvokedAtMostCountMatcher;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls as ConsecutiveCallsStub;
use PHPUnit\Framework\MockObject\Stub\Exception as ExceptionStub;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument as ReturnArgumentStub;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback as ReturnCallbackStub;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf as ReturnSelfStub;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap as ReturnValueMapStub;
function assertArrayHasKey($key, $array, string $message = ''): void
{
    Assert::assertArrayHasKey(...\func_get_args());
}
function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
{
    Assert::assertArraySubset(...\func_get_args());
}
function assertArrayNotHasKey($key, $array, string $message = ''): void
{
    Assert::assertArrayNotHasKey(...\func_get_args());
}
function assertContains($needle, $haystack, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertContains(...\func_get_args());
}
function assertAttributeContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertAttributeContains(...\func_get_args());
}
function assertNotContains($needle, $haystack, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertNotContains(...\func_get_args());
}
function assertAttributeNotContains($needle, string $haystackAttributeName, $haystackClassOrObject, string $message = '', bool $ignoreCase = false, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): void
{
    Assert::assertAttributeNotContains(...\func_get_args());
}
function assertContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertContainsOnly(...\func_get_args());
}
function assertContainsOnlyInstancesOf(string $className, iterable $haystack, string $message = ''): void
{
    Assert::assertContainsOnlyInstancesOf(...\func_get_args());
}
function assertAttributeContainsOnly(string $type, string $haystackAttributeName, $haystackClassOrObject, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertAttributeContainsOnly(...\func_get_args());
}
function assertNotContainsOnly(string $type, iterable $haystack, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertNotContainsOnly(...\func_get_args());
}
function assertAttributeNotContainsOnly(string $type, string $haystackAttributeName, $haystackClassOrObject, ?bool $isNativeType = null, string $message = ''): void
{
    Assert::assertAttributeNotContainsOnly(...\func_get_args());
}
function assertCount(int $expectedCount, $haystack, string $message = ''): void
{
    Assert::assertCount(...\func_get_args());
}
function assertAttributeCount(int $expectedCount, string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeCount(...\func_get_args());
}
function assertNotCount(int $expectedCount, $haystack, string $message = ''): void
{
    Assert::assertNotCount(...\func_get_args());
}
function assertAttributeNotCount(int $expectedCount, string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeNotCount(...\func_get_args());
}
function assertEquals($expected, $actual, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertEquals(...\func_get_args());
}
function assertAttributeEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertAttributeEquals(...\func_get_args());
}
function assertNotEquals($expected, $actual, string $message = '', $delta = 0.0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false): void
{
    Assert::assertNotEquals(...\func_get_args());
}
function assertAttributeNotEquals($expected, string $actualAttributeName, $actualClassOrObject, string $message = '', float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertAttributeNotEquals(...\func_get_args());
}
function assertEmpty($actual, string $message = ''): void
{
    Assert::assertEmpty(...\func_get_args());
}
function assertAttributeEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeEmpty(...\func_get_args());
}
function assertNotEmpty($actual, string $message = ''): void
{
    Assert::assertNotEmpty(...\func_get_args());
}
function assertAttributeNotEmpty(string $haystackAttributeName, $haystackClassOrObject, string $message = ''): void
{
    Assert::assertAttributeNotEmpty(...\func_get_args());
}
function assertGreaterThan($expected, $actual, string $message = ''): void
{
    Assert::assertGreaterThan(...\func_get_args());
}
function assertAttributeGreaterThan($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeGreaterThan(...\func_get_args());
}
function assertGreaterThanOrEqual($expected, $actual, string $message = ''): void
{
    Assert::assertGreaterThanOrEqual(...\func_get_args());
}
function assertAttributeGreaterThanOrEqual($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeGreaterThanOrEqual(...\func_get_args());
}
function assertLessThan($expected, $actual, string $message = ''): void
{
    Assert::assertLessThan(...\func_get_args());
}
function assertAttributeLessThan($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeLessThan(...\func_get_args());
}
function assertLessThanOrEqual($expected, $actual, string $message = ''): void
{
    Assert::assertLessThanOrEqual(...\func_get_args());
}
function assertAttributeLessThanOrEqual($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeLessThanOrEqual(...\func_get_args());
}
function assertFileEquals(string $expected, string $actual, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertFileEquals(...\func_get_args());
}
function assertFileNotEquals(string $expected, string $actual, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertFileNotEquals(...\func_get_args());
}
function assertStringEqualsFile(string $expectedFile, string $actualString, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertStringEqualsFile(...\func_get_args());
}
function assertStringNotEqualsFile(string $expectedFile, string $actualString, string $message = '', bool $canonicalize = false, bool $ignoreCase = false): void
{
    Assert::assertStringNotEqualsFile(...\func_get_args());
}
function assertIsReadable(string $filename, string $message = ''): void
{
    Assert::assertIsReadable(...\func_get_args());
}
function assertNotIsReadable(string $filename, string $message = ''): void
{
    Assert::assertNotIsReadable(...\func_get_args());
}
function assertIsWritable(string $filename, string $message = ''): void
{
    Assert::assertIsWritable(...\func_get_args());
}
function assertNotIsWritable(string $filename, string $message = ''): void
{
    Assert::assertNotIsWritable(...\func_get_args());
}
function assertDirectoryExists(string $directory, string $message = ''): void
{
    Assert::assertDirectoryExists(...\func_get_args());
}
function assertDirectoryNotExists(string $directory, string $message = ''): void
{
    Assert::assertDirectoryNotExists(...\func_get_args());
}
function assertDirectoryIsReadable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryIsReadable(...\func_get_args());
}
function assertDirectoryNotIsReadable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryNotIsReadable(...\func_get_args());
}
function assertDirectoryIsWritable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryIsWritable(...\func_get_args());
}
function assertDirectoryNotIsWritable(string $directory, string $message = ''): void
{
    Assert::assertDirectoryNotIsWritable(...\func_get_args());
}
function assertFileExists(string $filename, string $message = ''): void
{
    Assert::assertFileExists(...\func_get_args());
}
function assertFileNotExists(string $filename, string $message = ''): void
{
    Assert::assertFileNotExists(...\func_get_args());
}
function assertFileIsReadable(string $file, string $message = ''): void
{
    Assert::assertFileIsReadable(...\func_get_args());
}
function assertFileNotIsReadable(string $file, string $message = ''): void
{
    Assert::assertFileNotIsReadable(...\func_get_args());
}
function assertFileIsWritable(string $file, string $message = ''): void
{
    Assert::assertFileIsWritable(...\func_get_args());
}
function assertFileNotIsWritable(string $file, string $message = ''): void
{
    Assert::assertFileNotIsWritable(...\func_get_args());
}
function assertTrue($condition, string $message = ''): void
{
    Assert::assertTrue(...\func_get_args());
}
function assertNotTrue($condition, string $message = ''): void
{
    Assert::assertNotTrue(...\func_get_args());
}
function assertFalse($condition, string $message = ''): void
{
    Assert::assertFalse(...\func_get_args());
}
function assertNotFalse($condition, string $message = ''): void
{
    Assert::assertNotFalse(...\func_get_args());
}
function assertNull($actual, string $message = ''): void
{
    Assert::assertNull(...\func_get_args());
}
function assertNotNull($actual, string $message = ''): void
{
    Assert::assertNotNull(...\func_get_args());
}
function assertFinite($actual, string $message = ''): void
{
    Assert::assertFinite(...\func_get_args());
}
function assertInfinite($actual, string $message = ''): void
{
    Assert::assertInfinite(...\func_get_args());
}
function assertNan($actual, string $message = ''): void
{
    Assert::assertNan(...\func_get_args());
}
function assertClassHasAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassHasAttribute(...\func_get_args());
}
function assertClassNotHasAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassNotHasAttribute(...\func_get_args());
}
function assertClassHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassHasStaticAttribute(...\func_get_args());
}
function assertClassNotHasStaticAttribute(string $attributeName, string $className, string $message = ''): void
{
    Assert::assertClassNotHasStaticAttribute(...\func_get_args());
}
function assertObjectHasAttribute(string $attributeName, $object, string $message = ''): void
{
    Assert::assertObjectHasAttribute(...\func_get_args());
}
function assertObjectNotHasAttribute(string $attributeName, $object, string $message = ''): void
{
    Assert::assertObjectNotHasAttribute(...\func_get_args());
}
function assertSame($expected, $actual, string $message = ''): void
{
    Assert::assertSame(...\func_get_args());
}
function assertAttributeSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeSame(...\func_get_args());
}
function assertNotSame($expected, $actual, string $message = ''): void
{
    Assert::assertNotSame(...\func_get_args());
}
function assertAttributeNotSame($expected, string $actualAttributeName, $actualClassOrObject, string $message = ''): void
{
    Assert::assertAttributeNotSame(...\func_get_args());
}
function assertInstanceOf(string $expected, $actual, string $message = ''): void
{
    Assert::assertInstanceOf(...\func_get_args());
}
function assertAttributeInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeInstanceOf(...\func_get_args());
}
function assertNotInstanceOf(string $expected, $actual, string $message = ''): void
{
    Assert::assertNotInstanceOf(...\func_get_args());
}
function assertAttributeNotInstanceOf(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeNotInstanceOf(...\func_get_args());
}
function assertInternalType(string $expected, $actual, string $message = ''): void
{
    Assert::assertInternalType(...\func_get_args());
}
function assertAttributeInternalType(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeInternalType(...\func_get_args());
}
function assertNotInternalType(string $expected, $actual, string $message = ''): void
{
    Assert::assertNotInternalType(...\func_get_args());
}
function assertAttributeNotInternalType(string $expected, string $attributeName, $classOrObject, string $message = ''): void
{
    Assert::assertAttributeNotInternalType(...\func_get_args());
}
function assertRegExp(string $pattern, string $string, string $message = ''): void
{
    Assert::assertRegExp(...\func_get_args());
}
function assertNotRegExp(string $pattern, string $string, string $message = ''): void
{
    Assert::assertNotRegExp(...\func_get_args());
}
function assertSameSize($expected, $actual, string $message = ''): void
{
    Assert::assertSameSize(...\func_get_args());
}
function assertNotSameSize($expected, $actual, string $message = ''): void
{
    Assert::assertNotSameSize(...\func_get_args());
}
function assertStringMatchesFormat(string $format, string $string, string $message = ''): void
{
    Assert::assertStringMatchesFormat(...\func_get_args());
}
function assertStringNotMatchesFormat(string $format, string $string, string $message = ''): void
{
    Assert::assertStringNotMatchesFormat(...\func_get_args());
}
function assertStringMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
{
    Assert::assertStringMatchesFormatFile(...\func_get_args());
}
function assertStringNotMatchesFormatFile(string $formatFile, string $string, string $message = ''): void
{
    Assert::assertStringNotMatchesFormatFile(...\func_get_args());
}
function assertStringStartsWith(string $prefix, string $string, string $message = ''): void
{
    Assert::assertStringStartsWith(...\func_get_args());
}
function assertStringStartsNotWith($prefix, $string, string $message = ''): void
{
    Assert::assertStringStartsNotWith(...\func_get_args());
}
function assertStringEndsWith(string $suffix, string $string, string $message = ''): void
{
    Assert::assertStringEndsWith(...\func_get_args());
}
function assertStringEndsNotWith(string $suffix, string $string, string $message = ''): void
{
    Assert::assertStringEndsNotWith(...\func_get_args());
}
function assertXmlFileEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertXmlFileEqualsXmlFile(...\func_get_args());
}
function assertXmlFileNotEqualsXmlFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertXmlFileNotEqualsXmlFile(...\func_get_args());
}
function assertXmlStringEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringEqualsXmlFile(...\func_get_args());
}
function assertXmlStringNotEqualsXmlFile(string $expectedFile, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringNotEqualsXmlFile(...\func_get_args());
}
function assertXmlStringEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringEqualsXmlString(...\func_get_args());
}
function assertXmlStringNotEqualsXmlString($expectedXml, $actualXml, string $message = ''): void
{
    Assert::assertXmlStringNotEqualsXmlString(...\func_get_args());
}
function assertEqualXMLStructure(DOMElement $expectedElement, DOMElement $actualElement, bool $checkAttributes = false, string $message = ''): void
{
    Assert::assertEqualXMLStructure(...\func_get_args());
}
function assertThat($value, Constraint $constraint, string $message = ''): void
{
    Assert::assertThat(...\func_get_args());
}
function assertJson(string $actualJson, string $message = ''): void
{
    Assert::assertJson(...\func_get_args());
}
function assertJsonStringEqualsJsonString(string $expectedJson, string $actualJson, string $message = ''): void
{
    Assert::assertJsonStringEqualsJsonString(...\func_get_args());
}
function assertJsonStringNotEqualsJsonString($expectedJson, $actualJson, string $message = ''): void
{
    Assert::assertJsonStringNotEqualsJsonString(...\func_get_args());
}
function assertJsonStringEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
{
    Assert::assertJsonStringEqualsJsonFile(...\func_get_args());
}
function assertJsonStringNotEqualsJsonFile(string $expectedFile, string $actualJson, string $message = ''): void
{
    Assert::assertJsonStringNotEqualsJsonFile(...\func_get_args());
}
function assertJsonFileEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertJsonFileEqualsJsonFile(...\func_get_args());
}
function assertJsonFileNotEqualsJsonFile(string $expectedFile, string $actualFile, string $message = ''): void
{
    Assert::assertJsonFileNotEqualsJsonFile(...\func_get_args());
}
function logicalAnd(): LogicalAnd
{
    return Assert::logicalAnd(...\func_get_args());
}
function logicalOr(): LogicalOr
{
    return Assert::logicalOr(...\func_get_args());
}
function logicalNot(Constraint $constraint): LogicalNot
{
    return Assert::logicalNot(...\func_get_args());
}
function logicalXor(): LogicalXor
{
    return Assert::logicalXor(...\func_get_args());
}
function anything(): IsAnything
{
    return Assert::anything();
}
function isTrue(): IsTrue
{
    return Assert::isTrue();
}
function callback(callable $callback): Callback
{
    return Assert::callback(...\func_get_args());
}
function isFalse(): IsFalse
{
    return Assert::isFalse();
}
function isJson(): IsJson
{
    return Assert::isJson();
}
function isNull(): IsNull
{
    return Assert::isNull();
}
function isFinite(): IsFinite
{
    return Assert::isFinite();
}
function isInfinite(): IsInfinite
{
    return Assert::isInfinite();
}
function isNan(): IsNan
{
    return Assert::isNan();
}
function attribute(Constraint $constraint, string $attributeName): Attribute
{
    return Assert::attribute(...\func_get_args());
}
function contains($value, bool $checkForObjectIdentity = true, bool $checkForNonObjectIdentity = false): TraversableContains
{
    return Assert::contains(...\func_get_args());
}
function containsOnly(string $type): TraversableContainsOnly
{
    return Assert::containsOnly(...\func_get_args());
}
function containsOnlyInstancesOf(string $className): TraversableContainsOnly
{
    return Assert::containsOnlyInstancesOf(...\func_get_args());
}
function arrayHasKey($key): ArrayHasKey
{
    return Assert::arrayHasKey(...\func_get_args());
}
function equalTo($value, float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): IsEqual
{
    return Assert::equalTo(...\func_get_args());
}
function attributeEqualTo(string $attributeName, $value, float $delta = 0.0, int $maxDepth = 10, bool $canonicalize = false, bool $ignoreCase = false): Attribute
{
    return Assert::attributeEqualTo(...\func_get_args());
}
function isEmpty(): IsEmpty
{
    return Assert::isEmpty();
}
function isWritable(): IsWritable
{
    return Assert::isWritable();
}
function isReadable(): IsReadable
{
    return Assert::isReadable();
}
function directoryExists(): DirectoryExists
{
    return Assert::directoryExists();
}
function fileExists(): FileExists
{
    return Assert::fileExists();
}
function greaterThan($value): GreaterThan
{
    return Assert::greaterThan(...\func_get_args());
}
function greaterThanOrEqual($value): LogicalOr
{
    return Assert::greaterThanOrEqual(...\func_get_args());
}
function classHasAttribute(string $attributeName): ClassHasAttribute
{
    return Assert::classHasAttribute(...\func_get_args());
}
function classHasStaticAttribute(string $attributeName): ClassHasStaticAttribute
{
    return Assert::classHasStaticAttribute(...\func_get_args());
}
function objectHasAttribute($attributeName): ObjectHasAttribute
{
    return Assert::objectHasAttribute(...\func_get_args());
}
function identicalTo($value): IsIdentical
{
    return Assert::identicalTo(...\func_get_args());
}
function isInstanceOf(string $className): IsInstanceOf
{
    return Assert::isInstanceOf(...\func_get_args());
}
function isType(string $type): IsType
{
    return Assert::isType(...\func_get_args());
}
function lessThan($value): LessThan
{
    return Assert::lessThan(...\func_get_args());
}
function lessThanOrEqual($value): LogicalOr
{
    return Assert::lessThanOrEqual(...\func_get_args());
}
function matchesRegularExpression(string $pattern): RegularExpression
{
    return Assert::matchesRegularExpression(...\func_get_args());
}
function matches(string $string): StringMatchesFormatDescription
{
    return Assert::matches(...\func_get_args());
}
function stringStartsWith($prefix): StringStartsWith
{
    return Assert::stringStartsWith(...\func_get_args());
}
function stringContains(string $string, bool $case = true): StringContains
{
    return Assert::stringContains(...\func_get_args());
}
function stringEndsWith(string $suffix): StringEndsWith
{
    return Assert::stringEndsWith(...\func_get_args());
}
function countOf(int $count): Count
{
    return Assert::countOf(...\func_get_args());
}
function any(): AnyInvokedCountMatcher
{
    return new AnyInvokedCountMatcher;
}
function never(): InvokedCountMatcher
{
    return new InvokedCountMatcher(0);
}
function atLeast($requiredInvocations): InvokedAtLeastCountMatcher
{
    return new InvokedAtLeastCountMatcher(
        $requiredInvocations
    );
}
function atLeastOnce(): InvokedAtLeastOnceMatcher
{
    return new InvokedAtLeastOnceMatcher;
}
function once(): InvokedCountMatcher
{
    return new InvokedCountMatcher(1);
}
function exactly($count): InvokedCountMatcher
{
    return new InvokedCountMatcher($count);
}
function atMost($allowedInvocations): InvokedAtMostCountMatcher
{
    return new InvokedAtMostCountMatcher($allowedInvocations);
}
function at($index): InvokedAtIndexMatcher
{
    return new InvokedAtIndexMatcher($index);
}
function returnValue($value): ReturnStub
{
    return new ReturnStub($value);
}
function returnValueMap(array $valueMap): ReturnValueMapStub
{
    return new ReturnValueMapStub($valueMap);
}
function returnArgument($argumentIndex): ReturnArgumentStub
{
    return new ReturnArgumentStub($argumentIndex);
}
function returnCallback($callback): ReturnCallbackStub
{
    return new ReturnCallbackStub($callback);
}
function returnSelf(): ReturnSelfStub
{
    return new ReturnSelfStub;
}
function throwException(Throwable $exception): ExceptionStub
{
    return new ExceptionStub($exception);
}
function onConsecutiveCalls(): ConsecutiveCallsStub
{
    $args = \func_get_args();
    return new ConsecutiveCallsStub($args);
}