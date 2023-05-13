<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Iterator\MultiplePcreFilterIterator;
class MultiplePcreFilterIteratorTest extends TestCase
{
    public function testIsRegex($string, $isRegex, $message)
    {
        $testIterator = new TestMultiplePcreFilterIterator();
        $this->assertEquals($isRegex, $testIterator->isRegex($string), $message);
    }
    public function getIsRegexFixtures()
    {
        return [
            ['foo', false, 'string'],
            [' foo ', false, '" " is not a valid delimiter'],
            ['\\foo\\', false, '"\\" is not a valid delimiter'],
            ['afooa', false, '"a" is not a valid delimiter'],
            ['
            ['/a/', true, 'valid regex'],
            ['/foo/', true, 'valid regex'],
            ['/foo/i', true, 'valid regex with a single modifier'],
            ['/foo/imsxu', true, 'valid regex with multiple modifiers'],
            ['#foo#', true, '"#" is a valid delimiter'],
            ['{foo}', true, '"{,}" is a valid delimiter pair'],
            ['[foo]', true, '"[,]" is a valid delimiter pair'],
            ['(foo)', true, '"(,)" is a valid delimiter pair'],
            ['<foo>', true, '"<,>" is a valid delimiter pair'],
            ['*foo.*', false, '"*" is not considered as a valid delimiter'],
            ['?foo.?', false, '"?" is not considered as a valid delimiter'],
        ];
    }
}
class TestMultiplePcreFilterIterator extends MultiplePcreFilterIterator
{
    public function __construct()
    {
    }
    public function accept()
    {
        throw new \BadFunctionCallException('Not implemented');
    }
    public function isRegex($str)
    {
        return parent::isRegex($str);
    }
    public function toRegex($str)
    {
        throw new \BadFunctionCallException('Not implemented');
    }
}
