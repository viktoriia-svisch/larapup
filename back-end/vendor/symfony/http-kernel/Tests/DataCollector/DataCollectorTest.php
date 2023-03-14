<?php
namespace Symfony\Component\HttpKernel\Tests\DataCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Tests\Fixtures\DataCollector\CloneVarDataCollector;
use Symfony\Component\VarDumper\Cloner\VarCloner;
class DataCollectorTest extends TestCase
{
    public function testCloneVarStringWithScheme()
    {
        $c = new CloneVarDataCollector('scheme:
        $c->collect(new Request(), new Response());
        $cloner = new VarCloner();
        $this->assertEquals($cloner->cloneVar('scheme:
    }
    public function testCloneVarExistingFilePath()
    {
        $c = new CloneVarDataCollector([$filePath = tempnam(sys_get_temp_dir(), 'clone_var_data_collector_')]);
        $c->collect(new Request(), new Response());
        $this->assertSame($filePath, $c->getData()[0]);
    }
}
