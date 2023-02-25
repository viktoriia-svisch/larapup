<?php
namespace Symfony\Component\Console\Tests\Descriptor;
use Symfony\Component\Console\Descriptor\JsonDescriptor;
use Symfony\Component\Console\Output\BufferedOutput;
class JsonDescriptorTest extends AbstractDescriptorTest
{
    protected function getDescriptor()
    {
        return new JsonDescriptor();
    }
    protected function getFormat()
    {
        return 'json';
    }
    protected function assertDescription($expectedDescription, $describedObject, array $options = [])
    {
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, true);
        $this->getDescriptor()->describe($output, $describedObject, $options + ['raw_output' => true]);
        $this->assertEquals(json_decode(trim($expectedDescription), true), json_decode(trim(str_replace(PHP_EOL, "\n", $output->fetch())), true));
    }
}
