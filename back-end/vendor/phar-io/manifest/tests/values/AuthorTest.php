<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class AuthorTest extends TestCase {
    private $author;
    protected function setUp() {
        $this->author = new Author('Joe Developer', new Email('user@example.com'));
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(Author::class, $this->author);
    }
    public function testNameCanBeRetrieved() {
        $this->assertEquals('Joe Developer', $this->author->getName());
    }
    public function testEmailCanBeRetrieved() {
        $this->assertEquals('user@example.com', $this->author->getEmail());
    }
    public function testCanBeUsedAsString() {
        $this->assertEquals('Joe Developer <user@example.com>', $this->author);
    }
}
