<?php
namespace Symfony\Component\Routing\Tests\Generator\Dumper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
class PhpGeneratorDumperTest extends TestCase
{
    private $routeCollection;
    private $generatorDumper;
    private $testTmpFilepath;
    private $largeTestTmpFilepath;
    protected function setUp()
    {
        parent::setUp();
        $this->routeCollection = new RouteCollection();
        $this->generatorDumper = new PhpGeneratorDumper($this->routeCollection);
        $this->testTmpFilepath = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'php_generator.'.$this->getName().'.php';
        $this->largeTestTmpFilepath = sys_get_temp_dir().\DIRECTORY_SEPARATOR.'php_generator.'.$this->getName().'.large.php';
        @unlink($this->testTmpFilepath);
        @unlink($this->largeTestTmpFilepath);
    }
    protected function tearDown()
    {
        parent::tearDown();
        @unlink($this->testTmpFilepath);
        $this->routeCollection = null;
        $this->generatorDumper = null;
        $this->testTmpFilepath = null;
    }
    public function testDumpWithRoutes()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}'));
        $this->routeCollection->add('Test2', new Route('/testing2'));
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump());
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \ProjectUrlGenerator(new RequestContext('/app.php'));
        $absoluteUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_URL);
        $absoluteUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $relativeUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('http:
        $this->assertEquals('http:
        $this->assertEquals('/app.php/testing/bar', $relativeUrlWithParameter);
        $this->assertEquals('/app.php/testing2', $relativeUrlWithoutParameter);
    }
    public function testDumpWithSimpleLocalizedRoutes()
    {
        $this->routeCollection->add('test', (new Route('/foo')));
        $this->routeCollection->add('test.en', (new Route('/testing/is/fun'))->setDefault('_locale', 'en')->setDefault('_canonical_route', 'test'));
        $this->routeCollection->add('test.nl', (new Route('/testen/is/leuk'))->setDefault('_locale', 'nl')->setDefault('_canonical_route', 'test'));
        $code = $this->generatorDumper->dump([
            'class' => 'SimpleLocalizedProjectUrlGenerator',
        ]);
        file_put_contents($this->testTmpFilepath, $code);
        include $this->testTmpFilepath;
        $context = new RequestContext('/app.php');
        $projectUrlGenerator = new \SimpleLocalizedProjectUrlGenerator($context, null, 'en');
        $urlWithDefaultLocale = $projectUrlGenerator->generate('test');
        $urlWithSpecifiedLocale = $projectUrlGenerator->generate('test', ['_locale' => 'nl']);
        $context->setParameter('_locale', 'en');
        $urlWithEnglishContext = $projectUrlGenerator->generate('test');
        $context->setParameter('_locale', 'nl');
        $urlWithDutchContext = $projectUrlGenerator->generate('test');
        $this->assertEquals('/app.php/testing/is/fun', $urlWithDefaultLocale);
        $this->assertEquals('/app.php/testen/is/leuk', $urlWithSpecifiedLocale);
        $this->assertEquals('/app.php/testing/is/fun', $urlWithEnglishContext);
        $this->assertEquals('/app.php/testen/is/leuk', $urlWithDutchContext);
        $this->assertEquals('/app.php/testing/is/fun', $projectUrlGenerator->generate('test.en'));
        $context->setParameter('_locale', 'de_DE');
        $this->assertEquals('/app.php/foo', $projectUrlGenerator->generate('test'));
    }
    public function testDumpWithRouteNotFoundLocalizedRoutes()
    {
        $this->routeCollection->add('test.en', (new Route('/testing/is/fun'))->setDefault('_locale', 'en')->setDefault('_canonical_route', 'test'));
        $code = $this->generatorDumper->dump([
            'class' => 'RouteNotFoundLocalizedProjectUrlGenerator',
        ]);
        file_put_contents($this->testTmpFilepath, $code);
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \RouteNotFoundLocalizedProjectUrlGenerator(new RequestContext('/app.php'), null, 'pl_PL');
        $projectUrlGenerator->generate('test');
    }
    public function testDumpWithFallbackLocaleLocalizedRoutes()
    {
        $this->routeCollection->add('test.en', (new Route('/testing/is/fun'))->setDefault('_canonical_route', 'test'));
        $this->routeCollection->add('test.nl', (new Route('/testen/is/leuk'))->setDefault('_canonical_route', 'test'));
        $this->routeCollection->add('test.fr', (new Route('/tester/est/amusant'))->setDefault('_canonical_route', 'test'));
        $code = $this->generatorDumper->dump([
            'class' => 'FallbackLocaleLocalizedProjectUrlGenerator',
        ]);
        file_put_contents($this->testTmpFilepath, $code);
        include $this->testTmpFilepath;
        $context = new RequestContext('/app.php');
        $context->setParameter('_locale', 'en_GB');
        $projectUrlGenerator = new \FallbackLocaleLocalizedProjectUrlGenerator($context, null, null);
        $this->assertEquals('/app.php/testing/is/fun', $projectUrlGenerator->generate('test'));
        $this->assertEquals('/app.php/testen/is/leuk', $projectUrlGenerator->generate('test', ['_locale' => 'nl_BE']));
        $projectUrlGenerator = new \FallbackLocaleLocalizedProjectUrlGenerator(new RequestContext('/app.php'), null, 'fr_CA');
        $this->assertEquals('/app.php/tester/est/amusant', $projectUrlGenerator->generate('test'));
    }
    public function testDumpWithTooManyRoutes()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}'));
        for ($i = 0; $i < 32769; ++$i) {
            $this->routeCollection->add('route_'.$i, new Route('/route_'.$i));
        }
        $this->routeCollection->add('Test2', new Route('/testing2'));
        file_put_contents($this->largeTestTmpFilepath, $this->generatorDumper->dump([
            'class' => 'ProjectLargeUrlGenerator',
        ]));
        $this->routeCollection = $this->generatorDumper = null;
        include $this->largeTestTmpFilepath;
        $projectUrlGenerator = new \ProjectLargeUrlGenerator(new RequestContext('/app.php'));
        $absoluteUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_URL);
        $absoluteUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrlWithParameter = $projectUrlGenerator->generate('Test', ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $relativeUrlWithoutParameter = $projectUrlGenerator->generate('Test2', [], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('http:
        $this->assertEquals('http:
        $this->assertEquals('/app.php/testing/bar', $relativeUrlWithParameter);
        $this->assertEquals('/app.php/testing2', $relativeUrlWithoutParameter);
    }
    public function testDumpWithoutRoutes()
    {
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(['class' => 'WithoutRoutesUrlGenerator']));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \WithoutRoutesUrlGenerator(new RequestContext('/app.php'));
        $projectUrlGenerator->generate('Test', []);
    }
    public function testGenerateNonExistingRoute()
    {
        $this->routeCollection->add('Test', new Route('/test'));
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(['class' => 'NonExistingRoutesUrlGenerator']));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \NonExistingRoutesUrlGenerator(new RequestContext());
        $url = $projectUrlGenerator->generate('NonExisting', []);
    }
    public function testDumpForRouteWithDefaults()
    {
        $this->routeCollection->add('Test', new Route('/testing/{foo}', ['foo' => 'bar']));
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(['class' => 'DefaultRoutesUrlGenerator']));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \DefaultRoutesUrlGenerator(new RequestContext());
        $url = $projectUrlGenerator->generate('Test', []);
        $this->assertEquals('/testing', $url);
    }
    public function testDumpWithSchemeRequirement()
    {
        $this->routeCollection->add('Test1', new Route('/testing', [], [], [], '', ['ftp', 'https']));
        file_put_contents($this->testTmpFilepath, $this->generatorDumper->dump(['class' => 'SchemeUrlGenerator']));
        include $this->testTmpFilepath;
        $projectUrlGenerator = new \SchemeUrlGenerator(new RequestContext('/app.php'));
        $absoluteUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('ftp:
        $this->assertEquals('ftp:
        $projectUrlGenerator = new \SchemeUrlGenerator(new RequestContext('/app.php', 'GET', 'localhost', 'https'));
        $absoluteUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $relativeUrl = $projectUrlGenerator->generate('Test1', [], UrlGeneratorInterface::ABSOLUTE_PATH);
        $this->assertEquals('https:
        $this->assertEquals('/app.php/testing', $relativeUrl);
    }
}
