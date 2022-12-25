<?php
namespace Illuminate\Session;
use Illuminate\Support\Arr;
use SessionHandlerInterface;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\QueryException;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Container\Container;
class DatabaseSessionHandler implements SessionHandlerInterface, ExistenceAwareInterface
{
    use InteractsWithTime;
    protected $connection;
    protected $table;
    protected $minutes;
    protected $container;
    protected $exists;
    public function __construct(ConnectionInterface $connection, $table, $minutes, Container $container = null)
    {
        $this->table = $table;
        $this->minutes = $minutes;
        $this->container = $container;
        $this->connection = $connection;
    }
    public function open($savePath, $sessionName)
    {
        return true;
    }
    public function close()
    {
        return true;
    }
    public function read($sessionId)
    {
        $session = (object) $this->getQuery()->find($sessionId);
        if ($this->expired($session)) {
            $this->exists = true;
            return '';
        }
        if (isset($session->payload)) {
            $this->exists = true;
            return base64_decode($session->payload);
        }
        return '';
    }
    protected function expired($session)
    {
        return isset($session->last_activity) &&
            $session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp();
    }
    public function write($sessionId, $data)
    {
        $payload = $this->getDefaultPayload($data);
        if (! $this->exists) {
            $this->read($sessionId);
        }
        if ($this->exists) {
            $this->performUpdate($sessionId, $payload);
        } else {
            $this->performInsert($sessionId, $payload);
        }
        return $this->exists = true;
    }
    protected function performInsert($sessionId, $payload)
    {
        try {
            return $this->getQuery()->insert(Arr::set($payload, 'id', $sessionId));
        } catch (QueryException $e) {
            $this->performUpdate($sessionId, $payload);
        }
    }
    protected function performUpdate($sessionId, $payload)
    {
        return $this->getQuery()->where('id', $sessionId)->update($payload);
    }
    protected function getDefaultPayload($data)
    {
        $payload = [
            'payload' => base64_encode($data),
            'last_activity' => $this->currentTime(),
        ];
        if (! $this->container) {
            return $payload;
        }
        return tap($payload, function (&$payload) {
            $this->addUserInformation($payload)
                 ->addRequestInformation($payload);
        });
    }
    protected function addUserInformation(&$payload)
    {
        if ($this->container->bound(Guard::class)) {
            $payload['user_id'] = $this->userId();
        }
        return $this;
    }
    protected function userId()
    {
        return $this->container->make(Guard::class)->id();
    }
    protected function addRequestInformation(&$payload)
    {
        if ($this->container->bound('request')) {
            $payload = array_merge($payload, [
                'ip_address' => $this->ipAddress(),
                'user_agent' => $this->userAgent(),
            ]);
        }
        return $this;
    }
    protected function ipAddress()
    {
        return $this->container->make('request')->ip();
    }
    protected function userAgent()
    {
        return substr((string) $this->container->make('request')->header('User-Agent'), 0, 500);
    }
    public function destroy($sessionId)
    {
        $this->getQuery()->where('id', $sessionId)->delete();
        return true;
    }
    public function gc($lifetime)
    {
        $this->getQuery()->where('last_activity', '<=', $this->currentTime() - $lifetime)->delete();
    }
    protected function getQuery()
    {
        return $this->connection->table($this->table);
    }
    public function setExists($value)
    {
        $this->exists = $value;
        return $this;
    }
}
