<?php
namespace SebastianBergmann\CodeCoverage;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
final class Filter
{
    private $whitelistedFiles = [];
    private $isFileCallsCache = [];
    public function addDirectoryToWhitelist(string $directory, string $suffix = '.php', string $prefix = ''): void
    {
        $facade = new FileIteratorFacade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);
        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }
    public function addFileToWhitelist(string $filename): void
    {
        $this->whitelistedFiles[\realpath($filename)] = true;
    }
    public function addFilesToWhitelist(array $files): void
    {
        foreach ($files as $file) {
            $this->addFileToWhitelist($file);
        }
    }
    public function removeDirectoryFromWhitelist(string $directory, string $suffix = '.php', string $prefix = ''): void
    {
        $facade = new FileIteratorFacade;
        $files  = $facade->getFilesAsArray($directory, $suffix, $prefix);
        foreach ($files as $file) {
            $this->removeFileFromWhitelist($file);
        }
    }
    public function removeFileFromWhitelist(string $filename): void
    {
        $filename = \realpath($filename);
        unset($this->whitelistedFiles[$filename]);
    }
    public function isFile(string $filename): bool
    {
        if (isset($this->isFileCallsCache[$filename])) {
            return $this->isFileCallsCache[$filename];
        }
        if ($filename === '-' ||
            \strpos($filename, 'vfs:
            \strpos($filename, 'xdebug:
            \strpos($filename, 'eval()\'d code') !== false ||
            \strpos($filename, 'runtime-created function') !== false ||
            \strpos($filename, 'runkit created function') !== false ||
            \strpos($filename, 'assert code') !== false ||
            \strpos($filename, 'regexp code') !== false ||
            \strpos($filename, 'Standard input code') !== false) {
            $isFile = false;
        } else {
            $isFile = \file_exists($filename);
        }
        $this->isFileCallsCache[$filename] = $isFile;
        return $isFile;
    }
    public function isFiltered(string $filename): bool
    {
        if (!$this->isFile($filename)) {
            return true;
        }
        return !isset($this->whitelistedFiles[$filename]);
    }
    public function getWhitelist(): array
    {
        return \array_keys($this->whitelistedFiles);
    }
    public function hasWhitelist(): bool
    {
        return !empty($this->whitelistedFiles);
    }
    public function getWhitelistedFiles(): array
    {
        return $this->whitelistedFiles;
    }
    public function setWhitelistedFiles(array $whitelistedFiles): void
    {
        $this->whitelistedFiles = $whitelistedFiles;
    }
}
