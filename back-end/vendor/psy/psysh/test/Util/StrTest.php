<?php
namespace Psy\Test\Util;
use Psy\Util\Str;
class StrTest extends \PHPUnit\Framework\TestCase
{
    public function testUnvis($input, $expected)
    {
        $this->assertSame($expected, Str::unvis($input));
    }
    public function unvisProvider()
    {
        return \json_decode(\file_get_contents(__DIR__ . '/../fixtures/unvis_fixtures.json'));
    }
}
