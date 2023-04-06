<?php
namespace Composer\Repository\Vcs;
use Composer\Config;
use Composer\Util\ProcessExecutor;
use Composer\Util\Filesystem;
use Composer\IO\IOInterface;
class FossilDriver extends VcsDriver
{
    protected $tags;
    protected $branches;
    protected $rootIdentifier;
    protected $repoFile;
    protected $checkoutDir;
    protected $infoCache = array();
    public function initialize()
    {
        $this->checkFossil();
        $this->config->prohibitUrlByConfig($this->url, $this->io);
        if (Filesystem::isLocalPath($this->url) && is_dir($this->url)) {
            $this->checkoutDir = $this->url;
        } else {
            $localName = preg_replace('{[^a-z0-9]}i', '-', $this->url);
            $this->repoFile = $this->config->get('cache-repo-dir') . '/' . $localName . '.fossil';
            $this->checkoutDir = $this->config->get('cache-vcs-dir') . '/' . $localName . '/';
            $this->updateLocalRepo();
        }
        $this->getTags();
        $this->getBranches();
    }
    protected function checkFossil()
    {
        if (0 !== $this->process->execute('fossil version', $ignoredOutput)) {
            throw new \RuntimeException("fossil was not found, check that it is installed and in your PATH env.\n\n" . $this->process->getErrorOutput());
        }
    }
    protected function updateLocalRepo()
    {
        $fs = new Filesystem();
        $fs->ensureDirectoryExists($this->checkoutDir);
        if (!is_writable(dirname($this->checkoutDir))) {
            throw new \RuntimeException('Can not clone '.$this->url.' to access package information. The "'.$this->checkoutDir.'" directory is not writable by the current user.');
        }
        if (is_file($this->repoFile) && is_dir($this->checkoutDir) && 0 === $this->process->execute('fossil info', $output, $this->checkoutDir)) {
            if (0 !== $this->process->execute('fossil pull', $output, $this->checkoutDir)) {
                $this->io->writeError('<error>Failed to update '.$this->url.', package information from this repository may be outdated ('.$this->process->getErrorOutput().')</error>');
            }
        } else {
            $fs->removeDirectory($this->checkoutDir);
            $fs->remove($this->repoFile);
            $fs->ensureDirectoryExists($this->checkoutDir);
            if (0 !== $this->process->execute(sprintf('fossil clone %s %s', ProcessExecutor::escape($this->url), ProcessExecutor::escape($this->repoFile)), $output)) {
                $output = $this->process->getErrorOutput();
                throw new \RuntimeException('Failed to clone '.$this->url.' to repository ' . $this->repoFile . "\n\n" .$output);
            }
            if (0 !== $this->process->execute(sprintf('fossil open %s --nested', ProcessExecutor::escape($this->repoFile)), $output, $this->checkoutDir)) {
                $output = $this->process->getErrorOutput();
                throw new \RuntimeException('Failed to open repository '.$this->repoFile.' in ' . $this->checkoutDir . "\n\n" .$output);
            }
        }
    }
    public function getRootIdentifier()
    {
        if (null === $this->rootIdentifier) {
            $this->rootIdentifier = 'trunk';
        }
        return $this->rootIdentifier;
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function getSource($identifier)
    {
        return array('type' => 'fossil', 'url' => $this->getUrl(), 'reference' => $identifier);
    }
    public function getDist($identifier)
    {
        return null;
    }
    public function getFileContent($file, $identifier)
    {
        $command = sprintf('fossil cat -r %s %s', ProcessExecutor::escape($identifier), ProcessExecutor::escape($file));
        $this->process->execute($command, $content, $this->checkoutDir);
        if (!trim($content)) {
            return null;
        }
        return $content;
    }
    public function getChangeDate($identifier)
    {
        $this->process->execute('fossil finfo -b -n 1 composer.json', $output, $this->checkoutDir);
        list($ckout, $date, $message) = explode(' ', trim($output), 3);
        return new \DateTime($date, new \DateTimeZone('UTC'));
    }
    public function getTags()
    {
        if (null === $this->tags) {
            $tags = array();
            $this->process->execute('fossil tag list', $output, $this->checkoutDir);
            foreach ($this->process->splitLines($output) as $tag) {
                $tags[$tag] = $tag;
            }
            $this->tags = $tags;
        }
        return $this->tags;
    }
    public function getBranches()
    {
        if (null === $this->branches) {
            $branches = array();
            $bookmarks = array();
            $this->process->execute('fossil branch list', $output, $this->checkoutDir);
            foreach ($this->process->splitLines($output) as $branch) {
                $branch = trim(preg_replace('/^\*/', '', trim($branch)));
                $branches[$branch] = $branch;
            }
            $this->branches = $branches;
        }
        return $this->branches;
    }
    public static function supports(IOInterface $io, Config $config, $url, $deep = false)
    {
        if (preg_match('#(^(?:https?|ssh):
            return true;
        }
        if (preg_match('!/fossil/|\.fossil!', $url)) {
            return true;
        }
        if (Filesystem::isLocalPath($url)) {
            $url = Filesystem::getPlatformPath($url);
            if (!is_dir($url)) {
                return false;
            }
            $process = new ProcessExecutor($io);
            if ($process->execute('fossil info', $output, $url) === 0) {
                return true;
            }
        }
        return false;
    }
}
