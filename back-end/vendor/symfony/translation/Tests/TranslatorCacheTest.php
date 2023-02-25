<?php
namespace Symfony\Component\Translation\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Translator;
class TranslatorCacheTest extends TestCase
{
    protected $tmpDir;
    protected function setUp()
    {
        $this->tmpDir = sys_get_temp_dir().'/sf_translation';
        $this->deleteTmpDir();
    }
    protected function tearDown()
    {
        $this->deleteTmpDir();
    }
    protected function deleteTmpDir()
    {
        if (!file_exists($dir = $this->tmpDir)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->tmpDir), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $path) {
            if (preg_match('#[/\\\\]\.\.?$#', $path->__toString())) {
                continue;
            }
            if ($path->isDir()) {
                rmdir($path->__toString());
            } else {
                unlink($path->__toString());
            }
        }
        rmdir($this->tmpDir);
    }
    public function testThatACacheIsUsed($debug)
    {
        $locale = 'any_locale';
        $format = 'some_format';
        $msgid = 'test';
        $translator = new Translator($locale, null, $this->tmpDir, $debug);
        $translator->addLoader($format, new ArrayLoader());
        $translator->addResource($format, [$msgid => 'OK'], $locale);
        $translator->addResource($format, [$msgid.'+intl' => 'OK'], $locale, 'messages+intl-icu');
        $translator->trans($msgid);
        $translator->trans($msgid.'+intl', [], 'messages+intl-icu');
        $translator = new Translator($locale, null, $this->tmpDir, $debug);
        $translator->addLoader($format, $this->createFailingLoader());
        $translator->addResource($format, [$msgid => 'OK'], $locale);
        $translator->addResource($format, [$msgid.'+intl' => 'OK'], $locale, 'messages+intl-icu');
        $this->assertEquals('OK', $translator->trans($msgid), '-> caching does not work in '.($debug ? 'debug' : 'production'));
        $this->assertEquals('OK', $translator->trans($msgid.'+intl', [], 'messages+intl-icu'));
    }
    public function testCatalogueIsReloadedWhenResourcesAreNoLongerFresh()
    {
        $locale = 'any_locale';
        $format = 'some_format';
        $msgid = 'test';
        $catalogue = new MessageCatalogue($locale, []);
        $catalogue->addResource(new StaleResource()); 
        $loader = $this->getMockBuilder('Symfony\Component\Translation\Loader\LoaderInterface')->getMock();
        $loader
            ->expects($this->exactly(2))
            ->method('load')
            ->will($this->returnValue($catalogue))
        ;
        $translator = new Translator($locale, null, $this->tmpDir, true);
        $translator->addLoader($format, $loader);
        $translator->addResource($format, null, $locale);
        $translator->trans($msgid);
        $translator = new Translator($locale, null, $this->tmpDir, true);
        $translator->addLoader($format, $loader);
        $translator->addResource($format, null, $locale);
        $translator->trans($msgid);
    }
    public function testDifferentTranslatorsForSameLocaleDoNotOverwriteEachOthersCache($debug)
    {
        $locale = 'any_locale';
        $format = 'some_format';
        $msgid = 'test';
        $translator = new Translator($locale, null, $this->tmpDir, $debug);
        $translator->addLoader($format, new ArrayLoader());
        $translator->addResource($format, [$msgid => 'OK'], $locale);
        $translator->trans($msgid);
        $translator = new Translator($locale, null, $this->tmpDir, $debug);
        $translator->addLoader($format, new ArrayLoader());
        $translator->addResource($format, [$msgid => 'FAIL'], $locale);
        $translator->trans($msgid);
        $translator = new Translator($locale, null, $this->tmpDir, $debug);
        $translator->addLoader($format, $this->createFailingLoader());
        $translator->addResource($format, [$msgid => 'OK'], $locale);
        $this->assertEquals('OK', $translator->trans($msgid), '-> the cache was overwritten by another translator instance in '.($debug ? 'debug' : 'production'));
    }
    public function testGeneratedCacheFilesAreOnlyBelongRequestedLocales()
    {
        $translator = new Translator('a', null, $this->tmpDir);
        $translator->setFallbackLocales(['b']);
        $translator->trans('bar');
        $cachedFiles = glob($this->tmpDir.'
        $translator = new Translator('a', null, $this->tmpDir);
        $translator->setFallbackLocales(['b']);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['foo' => 'foo (a)'], 'a');
        $translator->addResource('array', ['bar' => 'bar (b)'], 'b');
        $this->assertEquals('bar (b)', $translator->trans('bar'));
        $translator->setFallbackLocales([]);
        $this->assertEquals('bar', $translator->trans('bar'));
        $translator = new Translator('a', null, $this->tmpDir);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['foo' => 'foo (a)'], 'a');
        $translator->addResource('array', ['bar' => 'bar (b)'], 'b');
        $this->assertEquals('bar', $translator->trans('bar'));
    }
    public function testPrimaryAndFallbackCataloguesContainTheSameMessagesRegardlessOfCaching()
    {
        $translator = new Translator('a', null, $this->tmpDir);
        $translator->setFallbackLocales(['b']);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['foo' => 'foo (a)'], 'a');
        $translator->addResource('array', ['foo' => 'foo (b)'], 'b');
        $translator->addResource('array', ['bar' => 'bar (b)'], 'b');
        $translator->addResource('array', ['baz' => 'baz (b)'], 'b', 'messages+intl-icu');
        $catalogue = $translator->getCatalogue('a');
        $this->assertFalse($catalogue->defines('bar')); 
        $fallback = $catalogue->getFallbackCatalogue();
        $this->assertTrue($fallback->defines('foo')); 
        $translator = new Translator('a', null, $this->tmpDir);
        $translator->setFallbackLocales(['b']);
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', ['foo' => 'foo (a)'], 'a');
        $translator->addResource('array', ['foo' => 'foo (b)'], 'b');
        $translator->addResource('array', ['bar' => 'bar (b)'], 'b');
        $translator->addResource('array', ['baz' => 'baz (b)'], 'b', 'messages+intl-icu');
        $catalogue = $translator->getCatalogue('a');
        $this->assertFalse($catalogue->defines('bar'));
        $fallback = $catalogue->getFallbackCatalogue();
        $this->assertTrue($fallback->defines('foo'));
        $this->assertTrue($fallback->defines('baz', 'messages+intl-icu'));
    }
    public function testRefreshCacheWhenResourcesAreNoLongerFresh()
    {
        $resource = $this->getMockBuilder('Symfony\Component\Config\Resource\SelfCheckingResourceInterface')->getMock();
        $loader = $this->getMockBuilder('Symfony\Component\Translation\Loader\LoaderInterface')->getMock();
        $resource->method('isFresh')->will($this->returnValue(false));
        $loader
            ->expects($this->exactly(2))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('fr', [], [$resource])));
        $translator = new Translator('fr', null, $this->tmpDir, true);
        $translator->addLoader('loader', $loader);
        $translator->addResource('loader', 'foo', 'fr');
        $translator->trans('foo');
        $translator = new Translator('fr', null, $this->tmpDir, true);
        $translator->addLoader('loader', $loader);
        $translator->addResource('loader', 'foo', 'fr');
        $translator->trans('foo');
    }
    protected function getCatalogue($locale, $messages, $resources = [])
    {
        $catalogue = new MessageCatalogue($locale);
        foreach ($messages as $key => $translation) {
            $catalogue->set($key, $translation);
        }
        foreach ($resources as $resource) {
            $catalogue->addResource($resource);
        }
        return $catalogue;
    }
    public function runForDebugAndProduction()
    {
        return [[true], [false]];
    }
    private function createFailingLoader()
    {
        $loader = $this->getMockBuilder('Symfony\Component\Translation\Loader\LoaderInterface')->getMock();
        $loader
            ->expects($this->never())
            ->method('load');
        return $loader;
    }
}
class StaleResource implements SelfCheckingResourceInterface
{
    public function isFresh($timestamp)
    {
        return false;
    }
    public function getResource()
    {
    }
    public function __toString()
    {
        return '';
    }
}
