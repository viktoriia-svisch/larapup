<?php
namespace Composer\Util;
use Composer\Config;
use Composer\IO\IOInterface;
class Hg
{
    private $io;
    private $config;
    private $process;
    public function __construct(IOInterface $io, Config $config, ProcessExecutor $process)
    {
        $this->io = $io;
        $this->config = $config;
        $this->process = $process;
    }
    public function runCommand($commandCallable, $url, $cwd)
    {
        $this->config->prohibitUrlByConfig($url, $this->io);
        $command = call_user_func($commandCallable, $url);
        if (0 === $this->process->execute($command, $ignoredOutput, $cwd)) {
            return;
        }
        if (preg_match('{^(https?):
            $auth = $this->io->getAuthentication($match[5]);
            $authenticatedUrl = $match[1] . ':
            $command = call_user_func($commandCallable, $authenticatedUrl);
            if (0 === $this->process->execute($command, $ignoredOutput, $cwd)) {
                return;
            }
            $error = $this->process->getErrorOutput();
        } else {
            $error = 'The given URL (' . $url . ') does not match the required format (http(s):
        }
        $this->throwException('Failed to clone ' . $url . ', ' . "\n\n" . $error, $url);
    }
    public static function sanitizeUrl($message)
    {
        return preg_replace_callback('{:
            if (preg_match('{^[a-f0-9]{12,}$}', $m[1])) {
                return ':
            }
            return ':
        }, $message);
    }
    private function throwException($message, $url)
    {
        if (0 !== $this->process->execute('hg --version', $ignoredOutput)) {
            throw new \RuntimeException(self::sanitizeUrl('Failed to clone ' . $url . ', hg was not found, check that it is installed and in your PATH env.' . "\n\n" . $this->process->getErrorOutput()));
        }
        throw new \RuntimeException(self::sanitizeUrl($message));
    }
}
