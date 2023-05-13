<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\RequirePass;
class RequirePassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new RequirePass());
    }
    public function testExitStatement($from, $to)
    {
        $this->assertProcessesAs($from, $to);
    }
    public function exitStatements()
    {
        $resolve = '\\Psy\\CodeCleaner\\RequirePass::resolve';
        return [
            ['require "a"', "require $resolve(\"a\", 1);"],
            ['require "b.php"', "require $resolve(\"b.php\", 1);"],
            ['require_once "c"', "require_once $resolve(\"c\", 1);"],
            ['require_once "d.php"', "require_once $resolve(\"d.php\", 1);"],
            ["null;\nrequire \"e.php\"", "null;\nrequire $resolve(\"e.php\", 2);"],
            ["null;\nrequire_once \"f.php\"", "null;\nrequire_once $resolve(\"f.php\", 2);"],
            ['require $foo', "require $resolve(\$foo, 1);"],
            ['require_once $foo', "require_once $resolve(\$foo, 1);"],
            ['require ($bar = "g.php")', "require $resolve(\$bar = \"g.php\", 1);"],
            ['require_once ($bar = "h.php")', "require_once $resolve(\$bar = \"h.php\", 1);"],
            ['$bar = require ($baz = "i.php")', "\$bar = (require $resolve(\$baz = \"i.php\", 1));"],
            ['$bar = require_once ($baz = "j.php")', "\$bar = (require_once $resolve(\$baz = \"j.php\", 1));"],
        ];
    }
    public function testResolve()
    {
        RequirePass::resolve('not a file name', 2);
    }
    public function testResolveEmptyWarnings($file)
    {
        if (!E_WARNING & \error_reporting()) {
            $this->markTestSkipped();
        }
        RequirePass::resolve($file, 1);
    }
    public function emptyWarnings()
    {
        return [
            [null],
            [false],
            [''],
        ];
    }
    public function testResolveWorks()
    {
        $this->assertEquals(__FILE__, RequirePass::resolve(__FILE__, 3));
    }
}
