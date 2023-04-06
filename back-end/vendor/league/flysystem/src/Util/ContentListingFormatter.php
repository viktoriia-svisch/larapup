<?php
namespace League\Flysystem\Util;
use League\Flysystem\Util;
class ContentListingFormatter
{
    private $directory;
    private $recursive;
    public function __construct($directory, $recursive)
    {
        $this->directory = $directory;
        $this->recursive = $recursive;
    }
    public function formatListing(array $listing)
    {
        $listing = array_values(
            array_map(
                [$this, 'addPathInfo'],
                array_filter($listing, [$this, 'isEntryOutOfScope'])
            )
        );
        return $this->sortListing($listing);
    }
    private function addPathInfo(array $entry)
    {
        return $entry + Util::pathinfo($entry['path']);
    }
    private function isEntryOutOfScope(array $entry)
    {
        if (empty($entry['path']) && $entry['path'] !== '0') {
            return false;
        }
        if ($this->recursive) {
            return $this->residesInDirectory($entry);
        }
        return $this->isDirectChild($entry);
    }
    private function residesInDirectory(array $entry)
    {
        if ($this->directory === '') {
            return true;
        }
        return strpos($entry['path'], $this->directory . '/') === 0;
    }
    private function isDirectChild(array $entry)
    {
        return Util::dirname($entry['path']) === $this->directory;
    }
    private function sortListing(array $listing)
    {
        usort($listing, function ($a, $b) {
            return strcasecmp($a['path'], $b['path']);
        });
        return $listing;
    }
}
