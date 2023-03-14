<?php
namespace Illuminate\Cache;
use Memcached;
class MemcachedConnector
{
    public function connect(array $servers, $connectionId = null, array $options = [], array $credentials = [])
    {
        $memcached = $this->getMemcached(
            $connectionId, $credentials, $options
        );
        if (! $memcached->getServerList()) {
            foreach ($servers as $server) {
                $memcached->addServer(
                    $server['host'], $server['port'], $server['weight']
                );
            }
        }
        return $memcached;
    }
    protected function getMemcached($connectionId, array $credentials, array $options)
    {
        $memcached = $this->createMemcachedInstance($connectionId);
        if (count($credentials) === 2) {
            $this->setCredentials($memcached, $credentials);
        }
        if (count($options)) {
            $memcached->setOptions($options);
        }
        return $memcached;
    }
    protected function createMemcachedInstance($connectionId)
    {
        return empty($connectionId) ? new Memcached : new Memcached($connectionId);
    }
    protected function setCredentials($memcached, $credentials)
    {
        [$username, $password] = $credentials;
        $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        $memcached->setSaslAuthData($username, $password);
    }
}
