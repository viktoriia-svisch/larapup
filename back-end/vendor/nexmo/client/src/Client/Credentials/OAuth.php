<?php
namespace Nexmo\Client\Credentials;
class OAuth extends AbstractCredentials implements CredentialsInterface
{
    public function __construct($consumerToken, $consumerSecret, $token, $secret)
    {
        $this->credentials = array_combine(array('consumer_key', 'consumer_secret', 'token', 'token_secret'), func_get_args());
    }
}
