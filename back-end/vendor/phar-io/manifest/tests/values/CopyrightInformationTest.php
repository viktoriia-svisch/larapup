<?php
namespace PharIo\Manifest;
use PHPUnit\Framework\TestCase;
class CopyrightInformationTest extends TestCase {
    private $copyrightInformation;
    private $author;
    private $license;
    protected function setUp() {
        $this->author  = new Author('Joe Developer', new Email('user@example.com'));
        $this->license = new License('BSD-3-Clause', new Url('https:
        $authors = new AuthorCollection;
        $authors->add($this->author);
        $this->copyrightInformation = new CopyrightInformation($authors, $this->license);
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(CopyrightInformation::class, $this->copyrightInformation);
    }
    public function testAuthorsCanBeRetrieved() {
        $this->assertContains($this->author, $this->copyrightInformation->getAuthors());
    }
    public function testLicenseCanBeRetrieved() {
        $this->assertEquals($this->license, $this->copyrightInformation->getLicense());
    }
}
