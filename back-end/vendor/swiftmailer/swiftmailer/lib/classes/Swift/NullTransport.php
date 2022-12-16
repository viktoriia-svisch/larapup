<?php
class Swift_NullTransport extends Swift_Transport_NullTransport
{
    public function __construct()
    {
        call_user_func_array(
            [$this, 'Swift_Transport_NullTransport::__construct'],
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.null')
        );
    }
}
