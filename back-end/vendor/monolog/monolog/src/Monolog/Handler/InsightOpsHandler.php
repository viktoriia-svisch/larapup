<?php
 namespace Monolog\Handler;
 use Monolog\Logger;
class InsightOpsHandler extends SocketHandler
{
    protected $logToken;
    public function __construct($token, $region = 'us', $useSSL = true, $level = Logger::DEBUG, $bubble = true)
    {
        if ($useSSL && !extension_loaded('openssl')) {
            throw new MissingExtensionException('The OpenSSL PHP plugin is required to use SSL encrypted connection for LogEntriesHandler');
        }
        $endpoint = $useSSL
            ? 'ssl:
            : $region . '.data.logs.insight.rapid7.com:80';
        parent::__construct($endpoint, $level, $bubble);
        $this->logToken = $token;
    }
    protected function generateDataStream($record)
    {
        return $this->logToken . ' ' . $record['formatted'];
    }
}
