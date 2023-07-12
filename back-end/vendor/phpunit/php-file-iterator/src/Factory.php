<?php
namespace SebastianBergmann\FileIterator;
class Factory
{
    public function getFileIterator($paths, $suffixes = '', $prefixes = '', array $exclude = []): \AppendIterator
    {
        if (\is_string($paths)) {
            $paths = [$paths];
        }
        $paths   = $this->getPathsAfterResolvingWildcards($paths);
        $exclude = $this->getPathsAfterResolvingWildcards($exclude);
        if (\is_string($prefixes)) {
            if ($prefixes !== '') {
                $prefixes = [$prefixes];
            } else {
                $prefixes = [];
            }
        }
        if (\is_string($suffixes)) {
            if ($suffixes !== '') {
                $suffixes = [$suffixes];
            } else {
                $suffixes = [];
            }
        }
        $iterator = new \AppendIterator;
        foreach ($paths as $path) {
            if (\is_dir($path)) {
                $iterator->append(
                    new Iterator(
                        $path,
                        new \RecursiveIteratorIterator(
                            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::FOLLOW_SYMLINKS | \RecursiveDirectoryIterator::SKIP_DOTS)
                        ),
                        $suffixes,
                        $prefixes,
                        $exclude
                    )
                );
            }
        }
        return $iterator;
    }
    protected function getPathsAfterResolvingWildcards(array $paths): array
    {
        $_paths = [];
        foreach ($paths as $path) {
            if ($locals = \glob($path, GLOB_ONLYDIR)) {
                $_paths = \array_merge($_paths, \array_map('\realpath', $locals));
            } else {
                $_paths[] = \realpath($path);
            }
        }
        return \array_filter($_paths);
    }
}
