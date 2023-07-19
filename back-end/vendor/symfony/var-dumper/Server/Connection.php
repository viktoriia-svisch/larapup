<?php
namespace Symfony\Component\VarDumper\Server;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
class Connection
{
    private $host;
    private $contextProviders;
    private $socket;
    public function __construct(string $host, array $contextProviders = [])
    {
        if (false === strpos($host, ':
            $host = 'tcp:
        }
        $this->host = $host;
        $this->contextProviders = $contextProviders;
    }
    public function getContextProviders(): array
    {
        return $this->contextProviders;
    }
    public function write(Data $data): bool
    {
        $socketIsFresh = !$this->socket;
        if (!$this->socket = $this->socket ?: $this->createSocket()) {
            return false;
        }
        $context = ['timestamp' => microtime(true)];
        foreach ($this->contextProviders as $name => $provider) {
            $context[$name] = $provider->getContext();
        }
        $context = array_filter($context);
        $encodedPayload = base64_encode(serialize([$data, $context]))."\n";
        set_error_handler([self::class, 'nullErrorHandler']);
        try {
            if (-1 !== stream_socket_sendto($this->socket, $encodedPayload)) {
                return true;
            }
            if (!$socketIsFresh) {
                stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
                fclose($this->socket);
                $this->socket = $this->createSocket();
            }
            if (-1 !== stream_socket_sendto($this->socket, $encodedPayload)) {
                return true;
            }
        } finally {
            restore_error_handler();
        }
        return false;
    }
    private static function nullErrorHandler($t, $m)
    {
    }
    private function createSocket()
    {
        set_error_handler([self::class, 'nullErrorHandler']);
        try {
            return stream_socket_client($this->host, $errno, $errstr, 3, STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT);
        } finally {
            restore_error_handler();
        }
    }
}