<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Support\ProcessUtils;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\PhpExecutableFinder;
class ServeCommand extends Command
{
    protected $name = 'serve';
    protected $description = 'Serve the application on the PHP development server';
    public function handle()
    {
        chdir(public_path());
        $this->line("<info>Laravel development server started:</info> <http:
        passthru($this->serverCommand(), $status);
        return $status;
    }
    protected function serverCommand()
    {
        return sprintf('%s -S %s:%s %s',
            ProcessUtils::escapeArgument((new PhpExecutableFinder)->find(false)),
            $this->host(),
            $this->port(),
            ProcessUtils::escapeArgument(base_path('server.php'))
        );
    }
    protected function host()
    {
        return $this->input->getOption('host');
    }
    protected function port()
    {
        return $this->input->getOption('port');
    }
    protected function getOptions()
    {
        return [
            ['host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on', '127.0.0.1'],
            ['port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on', 8000],
        ];
    }
}
