<?php
class Swift_SendmailTransport extends Swift_Transport_SendmailTransport
{
    public function __construct($command = '/usr/sbin/sendmail -bs')
    {
        call_user_func_array(
            [$this, 'Swift_Transport_SendmailTransport::__construct'],
            Swift_DependencyContainer::getInstance()
                ->createDependenciesFor('transport.sendmail')
            );
        $this->setCommand($command);
    }
}
