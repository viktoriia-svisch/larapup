<?php
namespace Symfony\Component\HttpKernel\CacheClearer;
class ChainCacheClearer implements CacheClearerInterface
{
    private $clearers;
    public function __construct(iterable $clearers = [])
    {
        $this->clearers = $clearers;
    }
    public function clear($cacheDir)
    {
        foreach ($this->clearers as $clearer) {
            $clearer->clear($cacheDir);
        }
    }
}
