<?php
namespace Psy\Test\CodeCleaner;
use PhpParser\NodeTraverser;
use Psy\CodeCleaner\CodeCleanerPass;
use Psy\Test\ParserTestCase;
class CodeCleanerTestCase extends ParserTestCase
{
    protected $pass;
    public function tearDown()
    {
        $this->pass = null;
        parent::tearDown();
    }
    protected function setPass(CodeCleanerPass $pass)
    {
        $this->pass = $pass;
        if (!isset($this->traverser)) {
            $this->traverser = new NodeTraverser();
        }
        $this->traverser->addVisitor($this->pass);
    }
    protected function parseAndTraverse($code, $prefix = '<?php ')
    {
        return $this->traverse($this->parse($code, $prefix));
    }
}
