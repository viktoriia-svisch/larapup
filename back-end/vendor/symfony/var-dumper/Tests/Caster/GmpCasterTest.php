<?php
namespace Symfony\Component\VarDumper\Tests\Caster;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Caster\GmpCaster;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;
class GmpCasterTest extends TestCase
{
    use VarDumperTestTrait;
    public function testCastGmp()
    {
        $gmpString = gmp_init('1234');
        $gmpOctal = gmp_init(010);
        $gmp = gmp_init('01101');
        $gmpDump = <<<EODUMP
array:1 [
  "\\x00~\\x00value" => %s
]
EODUMP;
        $this->assertDumpEquals(sprintf($gmpDump, $gmpString), GmpCaster::castGmp($gmpString, [], new Stub(), false, 0));
        $this->assertDumpEquals(sprintf($gmpDump, $gmpOctal), GmpCaster::castGmp($gmpOctal, [], new Stub(), false, 0));
        $this->assertDumpEquals(sprintf($gmpDump, $gmp), GmpCaster::castGmp($gmp, [], new Stub(), false, 0));
        $dump = <<<EODUMP
GMP {
  value: 577
}
EODUMP;
        $this->assertDumpEquals($dump, $gmp);
    }
}
