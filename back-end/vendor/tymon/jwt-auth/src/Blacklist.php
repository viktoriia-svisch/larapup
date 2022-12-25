<?php
namespace Tymon\JWTAuth;
use Tymon\JWTAuth\Support\Utils;
use Tymon\JWTAuth\Contracts\Providers\Storage;
class Blacklist
{
    protected $storage;
    protected $gracePeriod = 0;
    protected $refreshTTL = 20160;
    protected $key = 'jti';
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }
    public function add(Payload $payload)
    {
        if (! $payload->hasKey('exp')) {
            return $this->addForever($payload);
        }
        $this->storage->add(
            $this->getKey($payload),
            ['valid_until' => $this->getGraceTimestamp()],
            $this->getMinutesUntilExpired($payload)
        );
        return true;
    }
    protected function getMinutesUntilExpired(Payload $payload)
    {
        $exp = Utils::timestamp($payload['exp']);
        $iat = Utils::timestamp($payload['iat']);
        return $exp->max($iat->addMinutes($this->refreshTTL))->addMinute()->diffInMinutes();
    }
    public function addForever(Payload $payload)
    {
        $this->storage->forever($this->getKey($payload), 'forever');
        return true;
    }
    public function has(Payload $payload)
    {
        $val = $this->storage->get($this->getKey($payload));
        if ($val === 'forever') {
            return true;
        }
        return ! empty($val) && ! Utils::isFuture($val['valid_until']);
    }
    public function remove(Payload $payload)
    {
        return $this->storage->destroy($this->getKey($payload));
    }
    public function clear()
    {
        $this->storage->flush();
        return true;
    }
    protected function getGraceTimestamp()
    {
        return Utils::now()->addSeconds($this->gracePeriod)->getTimestamp();
    }
    public function setGracePeriod($gracePeriod)
    {
        $this->gracePeriod = (int) $gracePeriod;
        return $this;
    }
    public function getGracePeriod()
    {
        return $this->gracePeriod;
    }
    public function getKey(Payload $payload)
    {
        return $payload($this->key);
    }
    public function setKey($key)
    {
        $this->key = value($key);
        return $this;
    }
    public function setRefreshTTL($ttl)
    {
        $this->refreshTTL = (int) $ttl;
        return $this;
    }
    public function getRefreshTTL()
    {
        return $this->refreshTTL;
    }
}
