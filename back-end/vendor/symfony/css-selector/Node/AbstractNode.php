<?php
namespace Symfony\Component\CssSelector\Node;
abstract class AbstractNode implements NodeInterface
{
    private $nodeName;
    public function getNodeName(): string
    {
        if (null === $this->nodeName) {
            $this->nodeName = preg_replace('~.*\\\\([^\\\\]+)Node$~', '$1', \get_called_class());
        }
        return $this->nodeName;
    }
}
