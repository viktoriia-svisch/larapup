<?php
namespace Psy\Test\Command\TimeitCommand;
use PhpParser\NodeTraverser;
use Psy\Command\TimeitCommand\TimeitVisitor;
use Psy\Test\ParserTestCase;
class TimeitVisitorTest extends ParserTestCase
{
    public function setUp()
    {
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new TimeitVisitor());
    }
    public function testProcess($from, $to)
    {
        $this->assertProcessesAs($from, $to);
    }
    public function codez()
    {
        $start = '\Psy\Command\TimeitCommand::markStart';
        $end = '\Psy\Command\TimeitCommand::markEnd';
        $noReturn = 'new \Psy\CodeCleaner\NoReturnValue()';
        return [
            ['', "$end($start());"], 
            ['a()', "$start(); $end(a());"],
            ['$b()', "$start(); $end(\$b());"],
            ['$c->d()', "$start(); $end(\$c->d());"],
            ['e(); f()', "$start(); e(); $end(f());"],
            ['function g() { return 1; }', "$start(); function g() {return 1;} $end($noReturn);"],
            ['return 1', "$start(); return $end(1);"],
            ['return 1; 2', "$start(); return $end(1); $end(2);"],
            ['return 1; function h() {}', "$start(); return $end(1); function h() {} $end($noReturn);"],
        ];
    }
}
