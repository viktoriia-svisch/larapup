<?php
declare(strict_types=1);
namespace SebastianBergmann\GlobalState;
use PHPUnit\Framework\TestCase;
class RestorerTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        $GLOBALS['varBool'] = false;
        $GLOBALS['varNull'] = null;
        $_GET['varGet']     = 0;
    }
    public function testRestorerGlobalVariable()
    {
        $snapshot = new Snapshot(null, true, false, false, false, false, false, false, false, false);
        $restorer = new Restorer;
        $restorer->restoreGlobalVariables($snapshot);
        $this->assertArrayHasKey('varBool', $GLOBALS);
        $this->assertEquals(false, $GLOBALS['varBool']);
        $this->assertArrayHasKey('varNull', $GLOBALS);
        $this->assertEquals(null, $GLOBALS['varNull']);
        $this->assertArrayHasKey('varGet', $_GET);
        $this->assertEquals(0, $_GET['varGet']);
    }
    public function testIntegrationRestorerGlobalVariables()
    {
        $this->assertArrayHasKey('varBool', $GLOBALS);
        $this->assertEquals(false, $GLOBALS['varBool']);
        $this->assertArrayHasKey('varNull', $GLOBALS);
        $this->assertEquals(null, $GLOBALS['varNull']);
        $this->assertArrayHasKey('varGet', $_GET);
        $this->assertEquals(0, $_GET['varGet']);
    }
    public function testIntegrationRestorerGlobalVariables2()
    {
        $this->assertArrayHasKey('varBool', $GLOBALS);
        $this->assertEquals(false, $GLOBALS['varBool']);
        $this->assertArrayHasKey('varNull', $GLOBALS);
        $this->assertEquals(null, $GLOBALS['varNull']);
        $this->assertArrayHasKey('varGet', $_GET);
        $this->assertEquals(0, $_GET['varGet']);
    }
}
