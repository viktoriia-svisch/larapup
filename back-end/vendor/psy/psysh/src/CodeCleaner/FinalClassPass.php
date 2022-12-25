<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Psy\Exception\FatalErrorException;
class FinalClassPass extends CodeCleanerPass
{
    private $finalClasses;
    public function beforeTraverse(array $nodes)
    {
        $this->finalClasses = [];
    }
    public function enterNode(Node $node)
    {
        if ($node instanceof Class_) {
            if ($node->extends) {
                $extends = (string) $node->extends;
                if ($this->isFinalClass($extends)) {
                    $msg = \sprintf('Class %s may not inherit from final class (%s)', $node->name, $extends);
                    throw new FatalErrorException($msg, 0, E_ERROR, null, $node->getLine());
                }
            }
            if ($node->isFinal()) {
                $this->finalClasses[\strtolower($node->name)] = true;
            }
        }
    }
    private function isFinalClass($name)
    {
        if (!\class_exists($name)) {
            return isset($this->finalClasses[\strtolower($name)]);
        }
        $refl = new \ReflectionClass($name);
        return $refl->isFinal();
    }
}
