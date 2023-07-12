<?php
namespace Symfony\Component\Translation\Tests\Util;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Util\ArrayConverter;
class ArrayConverterTest extends TestCase
{
    public function testDump($input, $expectedOutput)
    {
        $this->assertEquals($expectedOutput, ArrayConverter::expandToTree($input));
    }
    public function messagesData()
    {
        return [
            [
                [
                    'foo1' => 'bar',
                    'foo.bar' => 'value',
                ],
                [
                    'foo1' => 'bar',
                    'foo' => ['bar' => 'value'],
                ],
            ],
            [
                [
                    'foo.bar' => 'value1',
                    'foo.bar.test' => 'value2',
                ],
                [
                    'foo' => [
                        'bar' => 'value1',
                        'bar.test' => 'value2',
                    ],
                ],
            ],
            [
                [
                    'foo.level2.level3.level4' => 'value1',
                    'foo.level2' => 'value2',
                    'foo.bar' => 'value3',
                ],
                [
                    'foo' => [
                        'level2' => 'value2',
                        'level2.level3.level4' => 'value1',
                        'bar' => 'value3',
                    ],
                ],
            ],
        ];
    }
}
