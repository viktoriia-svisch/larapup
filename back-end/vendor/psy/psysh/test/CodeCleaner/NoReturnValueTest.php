<?php
namespace Psy\Test\CodeCleaner;
use PhpParser\Node\Stmt\Expression;
use Psy\CodeCleaner\NoReturnValue;
use Psy\Test\ParserTestCase;
class NoReturnValueTest extends ParserTestCase
{
    public function testCreate()
    {
        $stmt = NoReturnValue::create();
        if (\class_exists('PhpParser\Node\Stmt\Expression')) {
            $stmt = new Expression($stmt);
        }
        $this->assertSame(
            $this->prettyPrint($this->parse('new \\Psy\CodeCleaner\\NoReturnValue()')),
            $this->prettyPrint([$stmt])
        );
    }
}
