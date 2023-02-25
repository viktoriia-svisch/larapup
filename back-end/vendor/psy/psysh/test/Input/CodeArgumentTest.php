<?php
namespace Psy\Test\Input;
use Psy\Input\CodeArgument;
use Symfony\Component\Console\Input\InputArgument;
class CodeArgumentTest extends \PHPUnit\Framework\TestCase
{
    public function testInvalidModes($mode)
    {
        new CodeArgument('wat', $mode);
    }
    public function getInvalidModes()
    {
        return [
            [InputArgument::IS_ARRAY],
            [InputArgument::IS_ARRAY | InputArgument::REQUIRED],
            [InputArgument::IS_ARRAY | InputArgument::OPTIONAL],
        ];
    }
    public function testValidModes($mode)
    {
        $this->assertInstanceOf('Psy\Input\CodeArgument', new CodeArgument('yeah', $mode));
    }
    public function getValidModes()
    {
        return [
            [InputArgument::REQUIRED],
            [InputArgument::OPTIONAL],
        ];
    }
}
