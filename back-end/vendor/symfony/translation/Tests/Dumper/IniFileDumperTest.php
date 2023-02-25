<?php
namespace Symfony\Component\Translation\Tests\Dumper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Dumper\IniFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
class IniFileDumperTest extends TestCase
{
    public function testFormatCatalogue()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(['foo' => 'bar']);
        $dumper = new IniFileDumper();
        $this->assertStringEqualsFile(__DIR__.'/../fixtures/resources.ini', $dumper->formatCatalogue($catalogue, 'messages'));
    }
}
