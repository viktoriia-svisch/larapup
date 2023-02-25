<?php
namespace Symfony\Component\Console\Tester;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
class ApplicationTester
{
    use TesterTrait;
    private $application;
    private $input;
    private $statusCode;
    public function __construct(Application $application)
    {
        $this->application = $application;
    }
    public function run(array $input, $options = [])
    {
        $this->input = new ArrayInput($input);
        if (isset($options['interactive'])) {
            $this->input->setInteractive($options['interactive']);
        }
        $shellInteractive = getenv('SHELL_INTERACTIVE');
        if ($this->inputs) {
            $this->input->setStream(self::createStream($this->inputs));
            putenv('SHELL_INTERACTIVE=1');
        }
        $this->initOutput($options);
        $this->statusCode = $this->application->run($this->input, $this->output);
        putenv($shellInteractive ? "SHELL_INTERACTIVE=$shellInteractive" : 'SHELL_INTERACTIVE');
        return $this->statusCode;
    }
}
