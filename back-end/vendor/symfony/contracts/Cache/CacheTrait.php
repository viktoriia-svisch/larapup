<?php
namespace Symfony\Contracts\Cache;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
trait CacheTrait
{
    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null)
    {
        return $this->doGet($this, $key, $callback, $beta, $metadata);
    }
    public function delete(string $key): bool
    {
        return $this->deleteItem($key);
    }
    private function doGet(CacheItemPoolInterface $pool, string $key, callable $callback, ?float $beta, array &$metadata = null)
    {
        if (0 > $beta = $beta ?? 1.0) {
            throw new class(sprintf('Argument "$beta" provided to "%s::get()" must be a positive number, %f given.', \get_class($this), $beta)) extends \InvalidArgumentException implements InvalidArgumentException {
            };
        }
        $item = $pool->getItem($key);
        $recompute = !$item->isHit() || INF === $beta;
        $metadata = $item instanceof ItemInterface ? $item->getMetadata() : array();
        if (!$recompute && $metadata) {
            $expiry = $metadata[ItemInterface::METADATA_EXPIRY] ?? false;
            $ctime = $metadata[ItemInterface::METADATA_CTIME] ?? false;
            if ($recompute = $ctime && $expiry && $expiry <= microtime(true) - $ctime / 1000 * $beta * log(random_int(1, PHP_INT_MAX) / PHP_INT_MAX)) {
                $item->expiresAt(null);
            }
        }
        if ($recompute) {
            $save = true;
            $item->set($callback($item, $save));
            if ($save) {
                $pool->save($item);
            }
        }
        return $item->get();
    }
}
