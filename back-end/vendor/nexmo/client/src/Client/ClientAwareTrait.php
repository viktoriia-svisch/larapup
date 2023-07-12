<?php
namespace Nexmo\Client;
use Nexmo\Client;
trait ClientAwareTrait
{
    protected $client;
    public function setClient(Client $client)
    {
        $this->client = $client;
    }
    protected function getClient()
    {
        if(isset($this->client)){
            return $this->client;
        }
        throw new \RuntimeException('Nexmo\Client not set');
    }
}
