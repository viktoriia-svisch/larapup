<?php
class Swift_KeyCache_SimpleKeyCacheInputStream implements Swift_KeyCache_KeyCacheInputStream
{
    private $keyCache;
    private $nsKey;
    private $itemKey;
    private $writeThrough = null;
    public function setKeyCache(Swift_KeyCache $keyCache)
    {
        $this->keyCache = $keyCache;
    }
    public function setWriteThroughStream(Swift_InputByteStream $is)
    {
        $this->writeThrough = $is;
    }
    public function write($bytes, Swift_InputByteStream $is = null)
    {
        $this->keyCache->setString(
            $this->nsKey, $this->itemKey, $bytes, Swift_KeyCache::MODE_APPEND
            );
        if (isset($is)) {
            $is->write($bytes);
        }
        if (isset($this->writeThrough)) {
            $this->writeThrough->write($bytes);
        }
    }
    public function commit()
    {
    }
    public function bind(Swift_InputByteStream $is)
    {
    }
    public function unbind(Swift_InputByteStream $is)
    {
    }
    public function flushBuffers()
    {
        $this->keyCache->clearKey($this->nsKey, $this->itemKey);
    }
    public function setNsKey($nsKey)
    {
        $this->nsKey = $nsKey;
    }
    public function setItemKey($itemKey)
    {
        $this->itemKey = $itemKey;
    }
    public function __clone()
    {
        $this->writeThrough = null;
    }
}
