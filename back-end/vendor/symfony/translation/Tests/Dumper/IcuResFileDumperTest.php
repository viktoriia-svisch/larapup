<?php
namespace Symfony\Component\Translation\Tests\Dumper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Dumper\IcuResFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
class IcuResFileDumperTest extends TestCase
{
    public function testFormatCatalogue()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(['foo' => 'bar']);
        $dumper = new IcuResFileDumper();
        $this->assertStringEqualsFile(__DIR__.'/../fixtures/resourcebundle/res/en.res', $dumper->formatCatalogue($catalogue, 'messages'));
    }
}
