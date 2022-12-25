<?php
namespace Symfony\Contracts\Cache;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;
interface ItemInterface extends CacheItemInterface
{
    const METADATA_EXPIRY = 'expiry';
    const METADATA_CTIME = 'ctime';
    const METADATA_TAGS = 'tags';
    public function tag($tags): self;
    public function getMetadata(): array;
}
