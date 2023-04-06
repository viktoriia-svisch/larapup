<?php
namespace Psy\VersionUpdater;
use Psy\Shell;
class GitHubChecker implements Checker
{
    const URL = 'https:
    private $latest;
    public function isLatest()
    {
        return \version_compare(Shell::VERSION, $this->getLatest(), '>=');
    }
    public function getLatest()
    {
        if (!isset($this->latest)) {
            $this->setLatest($this->getVersionFromTag());
        }
        return $this->latest;
    }
    public function setLatest($version)
    {
        $this->latest = $version;
    }
    private function getVersionFromTag()
    {
        $contents = $this->fetchLatestRelease();
        if (!$contents || !isset($contents->tag_name)) {
            throw new \InvalidArgumentException('Unable to check for updates');
        }
        $this->setLatest($contents->tag_name);
        return $this->getLatest();
    }
    public function fetchLatestRelease()
    {
        $context = \stream_context_create([
            'http' => [
                'user_agent' => 'PsySH/' . Shell::VERSION,
                'timeout'    => 3,
            ],
        ]);
        \set_error_handler(function () {
        });
        $result = @\file_get_contents(self::URL, false, $context);
        \restore_error_handler();
        return \json_decode($result);
    }
}
