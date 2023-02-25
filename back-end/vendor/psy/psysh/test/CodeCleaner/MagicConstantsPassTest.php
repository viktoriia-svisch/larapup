<?php
namespace Psy\Test\CodeCleaner;
use Psy\CodeCleaner\MagicConstantsPass;
class MagicConstantsPassTest extends CodeCleanerTestCase
{
    public function setUp()
    {
        $this->setPass(new MagicConstantsPass());
    }
    public function testProcess($from, $to)
    {
        $this->assertProcessesAs($from, $to);
    }
    public function magicConstants()
    {
        return [
            ['__DIR__;', 'getcwd();'],
            ['__FILE__;', "'';"],
            ['___FILE___;', '___FILE___;'],
        ];
    }
}
