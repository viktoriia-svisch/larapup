<?php
namespace SebastianBergmann\CodeCoverage\Report\Xml;
final class Tests
{
    private $contextNode;
    private $codeMap = [
        -1 => 'UNKNOWN',    
        0  => 'PASSED',     
        1  => 'SKIPPED',    
        2  => 'INCOMPLETE', 
        3  => 'FAILURE',    
        4  => 'ERROR',      
        5  => 'RISKY',      
        6  => 'WARNING',     
    ];
    public function __construct(\DOMElement $context)
    {
        $this->contextNode = $context;
    }
    public function addTest(string $test, array $result): void
    {
        $node = $this->contextNode->appendChild(
            $this->contextNode->ownerDocument->createElementNS(
                'https:
                'test'
            )
        );
        $node->setAttribute('name', $test);
        $node->setAttribute('size', $result['size']);
        $node->setAttribute('result', (int) $result['status']);
        $node->setAttribute('status', $this->codeMap[(int) $result['status']]);
    }
}
