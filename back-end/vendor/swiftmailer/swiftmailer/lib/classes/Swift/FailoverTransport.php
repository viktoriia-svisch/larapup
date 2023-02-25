<?php
class Swift_FailoverTransport extends Swift_Transport_FailoverTransport
{
    public function __construct($transports = [])
    {
        call_user_func_array(
            [$this, 'Swift_Transport_FailoverTransport::__construct'],
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.failover')
            );
        $this->setTransports($transports);
    }
}
