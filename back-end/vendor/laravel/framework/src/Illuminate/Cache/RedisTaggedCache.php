<?php
namespace Illuminate\Cache;
class RedisTaggedCache extends TaggedCache
{
    const REFERENCE_KEY_FOREVER = 'forever_ref';
    const REFERENCE_KEY_STANDARD = 'standard_ref';
    public function put($key, $value, $minutes = null)
    {
        $this->pushStandardKeys($this->tags->getNamespace(), $key);
        parent::put($key, $value, $minutes);
    }
    public function increment($key, $value = 1)
    {
        $this->pushStandardKeys($this->tags->getNamespace(), $key);
        parent::increment($key, $value);
    }
    public function decrement($key, $value = 1)
    {
        $this->pushStandardKeys($this->tags->getNamespace(), $key);
        parent::decrement($key, $value);
    }
    public function forever($key, $value)
    {
        $this->pushForeverKeys($this->tags->getNamespace(), $key);
        parent::forever($key, $value);
    }
    public function flush()
    {
        $this->deleteForeverKeys();
        $this->deleteStandardKeys();
        return parent::flush();
    }
    protected function pushStandardKeys($namespace, $key)
    {
        $this->pushKeys($namespace, $key, self::REFERENCE_KEY_STANDARD);
    }
    protected function pushForeverKeys($namespace, $key)
    {
        $this->pushKeys($namespace, $key, self::REFERENCE_KEY_FOREVER);
    }
    protected function pushKeys($namespace, $key, $reference)
    {
        $fullKey = $this->store->getPrefix().sha1($namespace).':'.$key;
        foreach (explode('|', $namespace) as $segment) {
            $this->store->connection()->sadd($this->referenceKey($segment, $reference), $fullKey);
        }
    }
    protected function deleteForeverKeys()
    {
        $this->deleteKeysByReference(self::REFERENCE_KEY_FOREVER);
    }
    protected function deleteStandardKeys()
    {
        $this->deleteKeysByReference(self::REFERENCE_KEY_STANDARD);
    }
    protected function deleteKeysByReference($reference)
    {
        foreach (explode('|', $this->tags->getNamespace()) as $segment) {
            $this->deleteValues($segment = $this->referenceKey($segment, $reference));
            $this->store->connection()->del($segment);
        }
    }
    protected function deleteValues($referenceKey)
    {
        $values = array_unique($this->store->connection()->smembers($referenceKey));
        if (count($values) > 0) {
            foreach (array_chunk($values, 1000) as $valuesChunk) {
                call_user_func_array([$this->store->connection(), 'del'], $valuesChunk);
            }
        }
    }
    protected function referenceKey($segment, $suffix)
    {
        return $this->store->getPrefix().$segment.':'.$suffix;
    }
}
