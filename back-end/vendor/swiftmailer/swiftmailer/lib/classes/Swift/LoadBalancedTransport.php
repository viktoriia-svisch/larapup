<?php
class Swift_LoadBalancedTransport extends Swift_Transport_LoadBalancedTransport
{
    public function __construct($transports = [])
    {
        call_user_func_array(
            [$this, 'Swift_Transport_LoadBalancedTransport::__construct'],
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.loadbalanced')
            );
        $this->setTransports($transports);
    }
}
