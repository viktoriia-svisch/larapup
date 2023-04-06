<?php
namespace Nexmo\Client\Credentials;
class SignatureSecret extends AbstractCredentials implements CredentialsInterface
{
    public function __construct($key, $signature_secret, $method='md5hash')
    {
        $this->credentials['api_key'] = $key;
        $this->credentials['signature_secret'] = $signature_secret;
        $this->credentials['signature_method'] = $method;
    }
}
