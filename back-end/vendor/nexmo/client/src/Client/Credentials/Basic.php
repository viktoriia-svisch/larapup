<?php
namespace Nexmo\Client\Credentials;
class Basic extends AbstractCredentials implements CredentialsInterface
{
    public function __construct($key, $secret)
    {
        $this->credentials['api_key'] = $key;
        $this->credentials['api_secret'] = $secret;
    }
}
