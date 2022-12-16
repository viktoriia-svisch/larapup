<?php
namespace PharIo\Manifest;
use PharIo\Version\Version;
use PharIo\Version\AnyVersionConstraint;
use PHPUnit\Framework\TestCase;
class ManifestTest extends TestCase {
    private $name;
    private $version;
    private $type;
    private $copyrightInformation;
    private $requirements;
    private $bundledComponents;
    private $manifest;
    protected function setUp() {
        $this->version = new Version('5.6.5');
        $this->type = Type::application();
        $author  = new Author('Joe Developer', new Email('user@example.com'));
        $license = new License('BSD-3-Clause', new Url('https:
        $authors = new AuthorCollection;
        $authors->add($author);
        $this->copyrightInformation = new CopyrightInformation($authors, $license);
        $this->requirements = new RequirementCollection;
        $this->requirements->add(new PhpVersionRequirement(new AnyVersionConstraint));
        $this->bundledComponents = new BundledComponentCollection;
        $this->bundledComponents->add(new BundledComponent('phpunit/php-code-coverage', new Version('4.0.2')));
        $this->name = new ApplicationName('phpunit/phpunit');
        $this->manifest = new Manifest(
            $this->name,
            $this->version,
            $this->type,
            $this->copyrightInformation,
            $this->requirements,
            $this->bundledComponents
        );
    }
    public function testCanBeCreated() {
        $this->assertInstanceOf(Manifest::class, $this->manifest);
    }
    public function testNameCanBeRetrieved() {
        $this->assertEquals($this->name, $this->manifest->getName());
    }
    public function testVersionCanBeRetrieved() {
        $this->assertEquals($this->version, $this->manifest->getVersion());
    }
    public function testTypeCanBeRetrieved() {
        $this->assertEquals($this->type, $this->manifest->getType());
    }
    public function testTypeCanBeQueried() {
        $this->assertTrue($this->manifest->isApplication());
        $this->assertFalse($this->manifest->isLibrary());
        $this->assertFalse($this->manifest->isExtension());
    }
    public function testCopyrightInformationCanBeRetrieved() {
        $this->assertEquals($this->copyrightInformation, $this->manifest->getCopyrightInformation());
    }
    public function testRequirementsCanBeRetrieved() {
        $this->assertEquals($this->requirements, $this->manifest->getRequirements());
    }
    public function testBundledComponentsCanBeRetrieved() {
        $this->assertEquals($this->bundledComponents, $this->manifest->getBundledComponents());
    }
    public function testExtendedApplicationCanBeQueriedForExtension()
    {
        $appName = new ApplicationName('foo/bar');
        $manifest = new Manifest(
            new ApplicationName('foo/foo'),
            new Version('1.0.0'),
            Type::extension($appName, new AnyVersionConstraint),
            $this->copyrightInformation,
            new RequirementCollection,
            new BundledComponentCollection
        );
        $this->assertTrue($manifest->isExtensionFor($appName));
    }
    public function testNonExtensionReturnsFalseWhenQueriesForExtension() {
        $appName = new ApplicationName('foo/bar');
        $manifest = new Manifest(
            new ApplicationName('foo/foo'),
            new Version('1.0.0'),
            Type::library(),
            $this->copyrightInformation,
            new RequirementCollection,
            new BundledComponentCollection
        );
        $this->assertFalse($manifest->isExtensionFor($appName));
    }
    public function testExtendedApplicationCanBeQueriedForExtensionWithVersion()
    {
        $appName = new ApplicationName('foo/bar');
        $manifest = new Manifest(
            new ApplicationName('foo/foo'),
            new Version('1.0.0'),
            Type::extension($appName, new AnyVersionConstraint),
            $this->copyrightInformation,
            new RequirementCollection,
            new BundledComponentCollection
        );
        $this->assertTrue($manifest->isExtensionFor($appName, new Version('1.2.3')));
    }
}
