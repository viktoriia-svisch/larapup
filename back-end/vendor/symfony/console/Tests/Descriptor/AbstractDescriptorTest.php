<?php
namespace Symfony\Component\Console\Tests\Descriptor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
abstract class AbstractDescriptorTest extends TestCase
{
    public function testDescribeInputArgument(InputArgument $argument, $expectedDescription)
    {
        $this->assertDescription($expectedDescription, $argument);
    }
    public function testDescribeInputOption(InputOption $option, $expectedDescription)
    {
        $this->assertDescription($expectedDescription, $option);
    }
    public function testDescribeInputDefinition(InputDefinition $definition, $expectedDescription)
    {
        $this->assertDescription($expectedDescription, $definition);
    }
    public function testDescribeCommand(Command $command, $expectedDescription)
    {
        $this->assertDescription($expectedDescription, $command);
    }
    public function testDescribeApplication(Application $application, $expectedDescription)
    {
        foreach ($application->all() as $command) {
            $command->setHelp(str_replace('%command.full_name%', 'app/console %command.name%', $command->getHelp()));
        }
        $this->assertDescription($expectedDescription, $application);
    }
    public function getDescribeInputArgumentTestData()
    {
        return $this->getDescriptionTestData(ObjectsProvider::getInputArguments());
    }
    public function getDescribeInputOptionTestData()
    {
        return $this->getDescriptionTestData(ObjectsProvider::getInputOptions());
    }
    public function getDescribeInputDefinitionTestData()
    {
        return $this->getDescriptionTestData(ObjectsProvider::getInputDefinitions());
    }
    public function getDescribeCommandTestData()
    {
        return $this->getDescriptionTestData(ObjectsProvider::getCommands());
    }
    public function getDescribeApplicationTestData()
    {
        return $this->getDescriptionTestData(ObjectsProvider::getApplications());
    }
    abstract protected function getDescriptor();
    abstract protected function getFormat();
    protected function getDescriptionTestData(array $objects)
    {
        $data = [];
        foreach ($objects as $name => $object) {
            $description = file_get_contents(sprintf('%s/../Fixtures/%s.%s', __DIR__, $name, $this->getFormat()));
            $data[] = [$object, $description];
        }
        return $data;
    }
    protected function assertDescription($expectedDescription, $describedObject, array $options = [])
    {
        $output = new BufferedOutput(BufferedOutput::VERBOSITY_NORMAL, true);
        $this->getDescriptor()->describe($output, $describedObject, $options + ['raw_output' => true]);
        $this->assertEquals(trim($expectedDescription), trim(str_replace(PHP_EOL, "\n", $output->fetch())));
    }
}
