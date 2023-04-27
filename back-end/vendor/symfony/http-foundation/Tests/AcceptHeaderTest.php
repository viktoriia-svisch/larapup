<?php
namespace Symfony\Component\HttpFoundation\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\AcceptHeaderItem;
class AcceptHeaderTest extends TestCase
{
    public function testFirst()
    {
        $header = AcceptHeader::fromString('text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c');
        $this->assertSame('text/html', $header->first()->getValue());
    }
    public function testFromString($string, array $items)
    {
        $header = AcceptHeader::fromString($string);
        $parsed = array_values($header->all());
        foreach ($parsed as $item) {
            $item->setIndex(0);
        }
        $this->assertEquals($items, $parsed);
    }
    public function provideFromStringData()
    {
        return [
            ['', []],
            ['gzip', [new AcceptHeaderItem('gzip')]],
            ['gzip,deflate,sdch', [new AcceptHeaderItem('gzip'), new AcceptHeaderItem('deflate'), new AcceptHeaderItem('sdch')]],
            ["gzip, deflate\t,sdch", [new AcceptHeaderItem('gzip'), new AcceptHeaderItem('deflate'), new AcceptHeaderItem('sdch')]],
            ['"this;should,not=matter"', [new AcceptHeaderItem('this;should,not=matter')]],
        ];
    }
    public function testToString(array $items, $string)
    {
        $header = new AcceptHeader($items);
        $this->assertEquals($string, (string) $header);
    }
    public function provideToStringData()
    {
        return [
            [[], ''],
            [[new AcceptHeaderItem('gzip')], 'gzip'],
            [[new AcceptHeaderItem('gzip'), new AcceptHeaderItem('deflate'), new AcceptHeaderItem('sdch')], 'gzip,deflate,sdch'],
            [[new AcceptHeaderItem('this;should,not=matter')], 'this;should,not=matter'],
        ];
    }
    public function testFilter($string, $filter, array $values)
    {
        $header = AcceptHeader::fromString($string)->filter($filter);
        $this->assertEquals($values, array_keys($header->all()));
    }
    public function provideFilterData()
    {
        return [
            ['fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4', '/fr.*/', ['fr-FR', 'fr']],
        ];
    }
    public function testSorting($string, array $values)
    {
        $header = AcceptHeader::fromString($string);
        $this->assertEquals($values, array_keys($header->all()));
    }
    public function provideSortingData()
    {
        return [
            'quality has priority' => ['*;q=0.3,ISO-8859-1,utf-8;q=0.7', ['ISO-8859-1', 'utf-8', '*']],
            'order matters when q is equal' => ['*;q=0.3,ISO-8859-1;q=0.7,utf-8;q=0.7', ['ISO-8859-1', 'utf-8', '*']],
            'order matters when q is equal2' => ['*;q=0.3,utf-8;q=0.7,ISO-8859-1;q=0.7', ['utf-8', 'ISO-8859-1', '*']],
        ];
    }
    public function testDefaultValue($acceptHeader, $value, $expectedQuality)
    {
        $header = AcceptHeader::fromString($acceptHeader);
        $this->assertSame($expectedQuality, $header->get($value)->getQuality());
    }
    public function provideDefaultValueData()
    {
        yield ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, *;q=0.3', 'text/xml', 0.3];
        yield ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, **;q=0.3', 'text/html', 1.0];
        yield ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, **;q=0.3', '*', 0.3];
        yield ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, **', 'text/xml', 1.0];
        yield ['text/plain;q=0.5, text/html, text/x-dvi;q=0.8, **', 'text*', 'text/html', 1.0];
        yield ['text/plain;q=0.5, text/html, text*', 'text/x-dvi', 0.8];
        yield ['*;q=0.3, ISO-8859-1;q=0.7, utf-8;q=0.7', '*', 0.3];
        yield ['*;q=0.3, ISO-8859-1;q=0.7, utf-8;q=0.7', 'utf-8', 0.7];
        yield ['*;q=0.3, ISO-8859-1;q=0.7, utf-8;q=0.7', 'SHIFT_JIS', 0.3];
    }
}
