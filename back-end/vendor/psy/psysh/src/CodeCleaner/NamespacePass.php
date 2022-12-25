<?php
namespace Psy\CodeCleaner;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Psy\CodeCleaner;
class NamespacePass extends CodeCleanerPass
{
    private $namespace = null;
    private $cleaner;
    public function __construct(CodeCleaner $cleaner)
    {
        $this->cleaner = $cleaner;
    }
    public function beforeTraverse(array $nodes)
    {
        if (empty($nodes)) {
            return $nodes;
        }
        $last = \end($nodes);
        if ($last instanceof Namespace_) {
            $kind = $last->getAttribute('kind');
            if ($kind === null || $kind === Namespace_::KIND_SEMICOLON) {
                $this->setNamespace($last->name);
            } else {
                $this->setNamespace(null);
            }
            return $nodes;
        }
        return $this->namespace ? [new Namespace_($this->namespace, $nodes)] : $nodes;
    }
    private function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        $this->cleaner->setNamespace($namespace === null ? null : $namespace->parts);
    }
}
