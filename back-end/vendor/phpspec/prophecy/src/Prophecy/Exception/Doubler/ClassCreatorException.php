<?php
namespace Prophecy\Exception\Doubler;
use Prophecy\Doubler\Generator\Node\ClassNode;
class ClassCreatorException extends \RuntimeException implements DoublerException
{
    private $node;
    public function __construct($message, ClassNode $node)
    {
        parent::__construct($message);
        $this->node = $node;
    }
    public function getClassNode()
    {
        return $this->node;
    }
}
