<?php
namespace Nexmo\Client;
use Nexmo\Client;
interface ClientAwareInterface
{
    public function setClient(Client $client);
}
