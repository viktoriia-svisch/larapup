<?php
namespace Illuminate\Queue\Connectors;
use Aws\Sqs\SqsClient;
use Illuminate\Support\Arr;
use Illuminate\Queue\SqsQueue;
class SqsConnector implements ConnectorInterface
{
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }
        return new SqsQueue(
            new SqsClient($config), $config['queue'], $config['prefix'] ?? ''
        );
    }
    protected function getDefaultConfiguration(array $config)
    {
        return array_merge([
            'version' => 'latest',
            'http' => [
                'timeout' => 60,
                'connect_timeout' => 60,
            ],
        ], $config);
    }
}
