<?php
namespace Psy\Command;
use Psy\Context;
use Psy\ContextAware;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
class EditCommand extends Command implements ContextAware
{
    private $runtimeDir = '';
    private $context;
    public function __construct($runtimeDir, $name = null)
    {
        parent::__construct($name);
        $this->runtimeDir = $runtimeDir;
    }
    protected function configure()
    {
        $this
            ->setName('edit')
            ->setDefinition([
                new InputArgument('file', InputArgument::OPTIONAL, 'The file to open for editing. If this is not given, edits a temporary file.', null),
                new InputOption(
                    'exec',
                    'e',
                    InputOption::VALUE_NONE,
                    'Execute the file content after editing. This is the default when a file name argument is not given.',
                    null
                ),
                new InputOption(
                    'no-exec',
                    'E',
                    InputOption::VALUE_NONE,
                    'Do not execute the file content after editing. This is the default when a file name argument is given.',
                    null
                ),
            ])
            ->setDescription('Open an external editor. Afterwards, get produced code in input buffer.')
            ->setHelp('Set the EDITOR environment variable to something you\'d like to use.');
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('exec') &&
            $input->getOption('no-exec')) {
            throw new \InvalidArgumentException('The --exec and --no-exec flags are mutually exclusive');
        }
        $filePath = $this->extractFilePath($input->getArgument('file'));
        $execute = $this->shouldExecuteFile(
            $input->getOption('exec'),
            $input->getOption('no-exec'),
            $filePath
        );
        $shouldRemoveFile = false;
        if ($filePath === null) {
            $filePath = \tempnam($this->runtimeDir, 'psysh-edit-command');
            $shouldRemoveFile = true;
        }
        $editedContent = $this->editFile($filePath, $shouldRemoveFile);
        if ($execute) {
            $this->getApplication()->addInput($editedContent);
        }
    }
    private function shouldExecuteFile($execOption, $noExecOption, $filePath)
    {
        if ($execOption) {
            return true;
        }
        if ($noExecOption) {
            return false;
        }
        return $filePath === null;
    }
    private function extractFilePath($fileArgument)
    {
        if ($fileArgument !== null &&
            \strlen($fileArgument) > 0 &&
            $fileArgument[0] === '$') {
            $fileArgument = $this->context->get(\preg_replace('/^\$/', '', $fileArgument));
        }
        return $fileArgument;
    }
    private function editFile($filePath, $shouldRemoveFile)
    {
        $escapedFilePath = \escapeshellarg($filePath);
        $pipes = [];
        $proc = \proc_open((\getenv('EDITOR') ?: 'nano') . " {$escapedFilePath}", [STDIN, STDOUT, STDERR], $pipes);
        \proc_close($proc);
        $editedContent = @\file_get_contents($filePath);
        if ($shouldRemoveFile) {
            @\unlink($filePath);
        }
        if ($editedContent === false) {
            throw new \UnexpectedValueException("Reading {$filePath} returned false");
        }
        return $editedContent;
    }
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
}
