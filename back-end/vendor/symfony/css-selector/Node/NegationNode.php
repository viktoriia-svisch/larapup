<?php
namespace Symfony\Component\CssSelector\Node;
class NegationNode extends AbstractNode
{
    private $selector;
    private $subSelector;
    public function __construct(NodeInterface $selector, NodeInterface $subSelector)
    {
        $this->selector = $selector;
        $this->subSelector = $subSelector;
    }
    public function getSelector()
    {
        return $this->selector;
    }
    public function getSubSelector()
    {
        return $this->subSelector;
    }
    public function getSpecificity(): Specificity
    {
        return $this->selector->getSpecificity()->plus($this->subSelector->getSpecificity());
    }
    public function __toString(): string
    {
        return sprintf('%s[%s:not(%s)]', $this->getNodeName(), $this->selector, $this->subSelector);
    }
}
