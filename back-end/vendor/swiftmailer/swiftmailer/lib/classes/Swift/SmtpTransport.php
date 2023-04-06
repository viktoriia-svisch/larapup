<?php
class Swift_SmtpTransport extends Swift_Transport_EsmtpTransport
{
    public function __construct($host = 'localhost', $port = 25, $encryption = null)
    {
        call_user_func_array(
            [$this, 'Swift_Transport_EsmtpTransport::__construct'],
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.smtp')
            );
        $this->setHost($host);
        $this->setPort($port);
        $this->setEncryption($encryption);
    }
}
