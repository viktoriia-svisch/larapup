<?php
namespace PharIo\Manifest;
use PharIo\Version\ExactVersionConstraint;
use PHPUnit\Framework\TestCase;
class RequirementCollectionTest extends TestCase {
    private $collection;
    private $item;
    protected function setUp() {
        $this->collection = new RequirementCollection;
        $this->item       = new PhpVersionRequirement(new ExactVersionConstraint('7.1.0'));
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(RequirementCollection::class, $this->collection);
    }
    public function testCanBeCounted() {
        $this->collection->add($this->item);
        $this->assertCount(1, $this->collection);
    }
    public function testCanBeIterated() {
        $this->collection->add(new PhpVersionRequirement(new ExactVersionConstraint('5.6.0')));
        $this->collection->add($this->item);
        $this->assertContains($this->item, $this->collection);
    }
    public function testKeyPositionCanBeRetreived() {
        $this->collection->add($this->item);
        foreach($this->collection as $key => $item) {
            $this->assertEquals(0, $key);
        }
    }
}
