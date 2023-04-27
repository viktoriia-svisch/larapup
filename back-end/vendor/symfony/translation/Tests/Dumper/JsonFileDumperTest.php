<?php
namespace Symfony\Component\Translation\Tests\Dumper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Dumper\JsonFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
class JsonFileDumperTest extends TestCase
{
    public function testFormatCatalogue()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(['foo' => 'bar']);
        $dumper = new JsonFileDumper();
        $this->assertStringEqualsFile(__DIR__.'/../fixtures/resources.json', $dumper->formatCatalogue($catalogue, 'messages'));
    }
    public function testDumpWithCustomEncoding()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(['foo' => '"bar"']);
        $dumper = new JsonFileDumper();
        $this->assertStringEqualsFile(__DIR__.'/../fixtures/resources.dump.json', $dumper->formatCatalogue($catalogue, 'messages', ['json_encoding' => JSON_HEX_QUOT]));
    }
}
