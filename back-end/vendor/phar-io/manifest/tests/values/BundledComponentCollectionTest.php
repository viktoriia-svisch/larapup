<?php
namespace PharIo\Manifest;
use PharIo\Version\Version;
use PHPUnit\Framework\TestCase;
class BundledComponentCollectionTest extends TestCase {
    private $collection;
    private $item;
    protected function setUp() {
        $this->collection = new BundledComponentCollection;
        $this->item       = new BundledComponent('phpunit/php-code-coverage', new Version('4.0.2'));
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(BundledComponentCollection::class, $this->collection);
    }
    public function testCanBeCounted() {
        $this->collection->add($this->item);
        $this->assertCount(1, $this->collection);
    }
    public function testCanBeIterated() {
        $this->collection->add($this->createMock(BundledComponent::class));
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
