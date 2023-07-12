<?php
namespace Psy\Test\Reflection;
use Psy\Reflection\ReflectionLanguageConstruct;
class ReflectionLanguageConstructTest extends \PHPUnit\Framework\TestCase
{
    public function testConstruction($keyword)
    {
        $refl = new ReflectionLanguageConstruct($keyword);
        $this->assertEquals($keyword, $refl->getName());
        $this->assertEquals($keyword, (string) $refl);
    }
    public function testKnownLanguageConstructs($keyword)
    {
        $this->assertTrue(ReflectionLanguageConstruct::isLanguageConstruct($keyword));
    }
    public function testFileName($keyword)
    {
        $refl = new ReflectionLanguageConstruct($keyword);
        $this->assertFalse($refl->getFileName());
    }
    public function testReturnsReference($keyword)
    {
        $refl = new ReflectionLanguageConstruct($keyword);
        $this->assertFalse($refl->returnsReference());
    }
    public function testGetParameters($keyword)
    {
        $refl = new ReflectionLanguageConstruct($keyword);
        $this->assertNotEmpty($refl->getParameters());
    }
    public function testExportThrows($keyword)
    {
        ReflectionLanguageConstruct::export($keyword);
    }
    public function languageConstructs()
    {
        return [
            ['isset'],
            ['unset'],
            ['empty'],
            ['echo'],
            ['print'],
            ['die'],
            ['exit'],
        ];
    }
    public function testUnknownLanguageConstructsThrowExceptions($keyword)
    {
        new ReflectionLanguageConstruct($keyword);
    }
    public function unknownLanguageConstructs()
    {
        return [
            ['async'],
            ['await'],
            ['comefrom'],
        ];
    }
}
