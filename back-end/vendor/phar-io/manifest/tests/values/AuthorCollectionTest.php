<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class AuthorCollectionTest extends TestCase {
    private $collection;
    private $item;
    protected function setUp() {
        $this->collection = new AuthorCollection;
        $this->item       = new Author('Joe Developer', new Email('user@example.com'));
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(AuthorCollection::class, $this->collection);
    }
    public function testCanBeCounted() {
        $this->collection->add($this->item);
        $this->assertCount(1, $this->collection);
    }
    public function testCanBeIterated() {
        $this->collection->add(
            new Author('Dummy First', new Email('dummy@example.com'))
        );
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
