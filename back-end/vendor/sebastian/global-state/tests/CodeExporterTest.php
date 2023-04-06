<?php
declare(strict_types=1);
namespace SebastianBergmann\GlobalState;
use PHPUnit\Framework\TestCase;
class CodeExporterTest extends TestCase
{
    public function testCanExportGlobalVariablesToCode()
    {
        $GLOBALS = ['foo' => 'bar'];
        $snapshot = new Snapshot(null, true, false, false, false, false, false, false, false, false);
        $exporter = new CodeExporter;
        $this->assertEquals(
            '$GLOBALS = [];' . PHP_EOL . '$GLOBALS[\'foo\'] = \'bar\';' . PHP_EOL,
            $exporter->globalVariables($snapshot)
        );
    }
}
