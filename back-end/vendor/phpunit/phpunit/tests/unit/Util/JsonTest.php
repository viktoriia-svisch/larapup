<?php
namespace PHPUnit\Util;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;
class JsonTest extends TestCase
{
    public function testCanonicalize($actual, $expected, $expectError): void
    {
        [$error, $canonicalized] = Json::canonicalize($actual);
        $this->assertEquals($expectError, $error);
        if (!$expectError) {
            $this->assertEquals($expected, $canonicalized);
        }
    }
    public function canonicalizeProvider(): array
    {
        return [
            ['{"name":"John","age":"35"}', '{"age":"35","name":"John"}', false],
            ['{"name":"John","age":"35","kids":[{"name":"Petr","age":"5"}]}', '{"age":"35","kids":[{"age":"5","name":"Petr"}],"name":"John"}', false],
            ['"name":"John","age":"35"}', '{"age":"35","name":"John"}', true],
        ];
    }
    public function testPrettify($actual, $expected): void
    {
        $this->assertEquals($expected, Json::prettify($actual));
    }
    public function prettifyProvider(): array
    {
        return [
            ['{"name":"John","age": "5"}', "{\n    \"name\": \"John\",\n    \"age\": \"5\"\n}"],
            ['{"url":"https:
        ];
    }
    public function testPrettifyException($json): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot prettify invalid json');
        Json::prettify($json);
    }
    public function prettifyExceptionProvider(): array
    {
        return [
            ['"name":"John","age": "5"}'],
            [''],
        ];
    }
}
