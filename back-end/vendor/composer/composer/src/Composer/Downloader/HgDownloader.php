<?php
namespace Composer\Downloader;
use Composer\Package\PackageInterface;
use Composer\Util\ProcessExecutor;
use Composer\Util\Hg as HgUtils;
class HgDownloader extends VcsDownloader
{
    public function doDownload(PackageInterface $package, $path, $url)
    {
        $hgUtils = new HgUtils($this->io, $this->config, $this->process);
        $cloneCommand = function ($url) use ($path) {
            return sprintf('hg clone %s %s', ProcessExecutor::escape($url), ProcessExecutor::escape($path));
        };
        $hgUtils->runCommand($cloneCommand, $url, $path);
        $ref = ProcessExecutor::escape($package->getSourceReference());
        $command = sprintf('hg up %s', $ref);
        if (0 !== $this->process->execute($command, $ignoredOutput, realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
    }
    public function doUpdate(PackageInterface $initial, PackageInterface $target, $path, $url)
    {
        $hgUtils = new HgUtils($this->io, $this->config, $this->process);
        $ref = $target->getSourceReference();
        $this->io->writeError(" Updating to ".$target->getSourceReference());
        if (!$this->hasMetadataRepository($path)) {
            throw new \RuntimeException('The .hg directory is missing from '.$path.', see https:
        }
        $command = function ($url) use ($ref) {
            return sprintf('hg pull %s && hg up %s', ProcessExecutor::escape($url), ProcessExecutor::escape($ref));
        };
        $hgUtils->runCommand($command, $url, $path);
    }
    public function getLocalChanges(PackageInterface $package, $path)
    {
        if (!is_dir($path.'/.hg')) {
            return null;
        }
        $this->process->execute('hg st', $output, realpath($path));
        return trim($output) ?: null;
    }
    protected function getCommitLogs($fromReference, $toReference, $path)
    {
        $command = sprintf('hg log -r %s:%s --style compact', ProcessExecutor::escape($fromReference), ProcessExecutor::escape($toReference));
        if (0 !== $this->process->execute($command, $output, realpath($path))) {
            throw new \RuntimeException('Failed to execute ' . $command . "\n\n" . $this->process->getErrorOutput());
        }
        return $output;
    }
    protected function hasMetadataRepository($path)
    {
        return is_dir($path . '/.hg');
    }
}
