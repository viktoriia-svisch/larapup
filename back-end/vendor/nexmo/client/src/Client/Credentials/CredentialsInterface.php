<?php
namespace Nexmo\Client\Credentials;
interface CredentialsInterface extends \ArrayAccess
{
    public function asArray();
}
