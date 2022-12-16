<?php
require_once 'swift_required.php';
class Swift_DependencyContainerAcceptanceTest extends PHPUnit\Framework\TestCase
{
    public function testNoLookupsFail()
    {
        $di = Swift_DependencyContainer::getInstance();
        foreach ($di->listItems() as $itemName) {
            try {
                $di->lookup($itemName);
            } catch (Swift_DependencyException $e) {
                $this->fail($e->getMessage());
            }
        }
        $this->addToAssertionCount(1);
    }
}
