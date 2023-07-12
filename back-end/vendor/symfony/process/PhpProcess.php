<?php
namespace Symfony\Component\Process;
use Symfony\Component\Process\Exception\RuntimeException;
class PhpProcess extends Process
{
    public function __construct(string $script, string $cwd = null, array $env = null, int $timeout = 60, array $php = null)
    {
        $executableFinder = new PhpExecutableFinder();
        if (false === $php = $php ?? $executableFinder->find(false)) {
            $php = null;
        } else {
            $php = array_merge([$php], $executableFinder->findArguments());
        }
        if ('phpdbg' === \PHP_SAPI) {
            $file = tempnam(sys_get_temp_dir(), 'dbg');
            file_put_contents($file, $script);
            register_shutdown_function('unlink', $file);
            $php[] = $file;
            $script = null;
        }
        parent::__construct($php, $cwd, $env, $script, $timeout);
    }
    public function setPhpBinary($php)
    {
        @trigger_error(sprintf('The "%s()" method is deprecated since Symfony 4.2, use the $php argument of the constructor instead.', __METHOD__), E_USER_DEPRECATED);
        $this->setCommandLine($php);
    }
    public function start(callable $callback = null, array $env = [])
    {
        if (null === $this->getCommandLine()) {
            throw new RuntimeException('Unable to find the PHP executable.');
        }
        parent::start($callback, $env);
    }
}
