<?php
namespace Symfony\Component\Translation\Tests\Dumper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Dumper\PhpFileDumper;
use Symfony\Component\Translation\MessageCatalogue;
class PhpFileDumperTest extends TestCase
{
    public function testFormatCatalogue()
    {
        $catalogue = new MessageCatalogue('en');
        $catalogue->add(['foo' => 'bar']);
        $dumper = new PhpFileDumper();
        $this->assertStringEqualsFile(__DIR__.'/../fixtures/resources.php', $dumper->formatCatalogue($catalogue, 'messages'));
    }
}
