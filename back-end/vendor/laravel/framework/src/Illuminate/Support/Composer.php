<?php
namespace Illuminate\Support;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;
class Composer
{
    protected $files;
    protected $workingPath;
    public function __construct(Filesystem $files, $workingPath = null)
    {
        $this->files = $files;
        $this->workingPath = $workingPath;
    }
    public function dumpAutoloads($extra = '')
    {
        $process = $this->getProcess();
        $process->setCommandLine(trim($this->findComposer().' dump-autoload '.$extra));
        $process->run();
    }
    public function dumpOptimized()
    {
        $this->dumpAutoloads('--optimize');
    }
    protected function findComposer()
    {
        if ($this->files->exists($this->workingPath.'/composer.phar')) {
            return ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false)).' composer.phar';
        }
        return 'composer';
    }
    protected function getProcess()
    {
        return (new Process('', $this->workingPath))->setTimeout(null);
    }
    public function setWorkingPath($path)
    {
        $this->workingPath = realpath($path);
        return $this;
    }
}
