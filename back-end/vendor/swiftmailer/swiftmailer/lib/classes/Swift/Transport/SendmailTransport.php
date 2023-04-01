<?php
class Swift_Transport_SendmailTransport extends Swift_Transport_AbstractSmtpTransport
{
    private $params = [
        'timeout' => 30,
        'blocking' => 1,
        'command' => '/usr/sbin/sendmail -bs',
        'type' => Swift_Transport_IoBuffer::TYPE_PROCESS,
        ];
    public function __construct(Swift_Transport_IoBuffer $buf, Swift_Events_EventDispatcher $dispatcher, $localDomain = '127.0.0.1', Swift_AddressEncoder $addressEncoder = null)
    {
        parent::__construct($buf, $dispatcher, $localDomain, $addressEncoder);
    }
    public function start()
    {
        if (false !== strpos($this->getCommand(), ' -bs')) {
            parent::start();
        }
    }
    public function setCommand($command)
    {
        $this->params['command'] = $command;
        return $this;
    }
    public function getCommand()
    {
        return $this->params['command'];
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $failedRecipients = (array) $failedRecipients;
        $command = $this->getCommand();
        $buffer = $this->getBuffer();
        $count = 0;
        if (false !== strpos($command, ' -t')) {
            if ($evt = $this->eventDispatcher->createSendEvent($this, $message)) {
                $this->eventDispatcher->dispatchEvent($evt, 'beforeSendPerformed');
                if ($evt->bubbleCancelled()) {
                    return 0;
                }
            }
            if (false === strpos($command, ' -f')) {
                $command .= ' -f'.escapeshellarg($this->getReversePath($message));
            }
            $buffer->initialize(array_merge($this->params, ['command' => $command]));
            if (false === strpos($command, ' -i') && false === strpos($command, ' -oi')) {
                $buffer->setWriteTranslations(["\r\n" => "\n", "\n." => "\n.."]);
            } else {
                $buffer->setWriteTranslations(["\r\n" => "\n"]);
            }
            $count = count((array) $message->getTo())
                + count((array) $message->getCc())
                + count((array) $message->getBcc())
                ;
            $message->toByteStream($buffer);
            $buffer->flushBuffers();
            $buffer->setWriteTranslations([]);
            $buffer->terminate();
            if ($evt) {
                $evt->setResult(Swift_Events_SendEvent::RESULT_SUCCESS);
                $evt->setFailedRecipients($failedRecipients);
                $this->eventDispatcher->dispatchEvent($evt, 'sendPerformed');
            }
            $message->generateId();
        } elseif (false !== strpos($command, ' -bs')) {
            $count = parent::send($message, $failedRecipients);
        } else {
            $this->throwException(new Swift_TransportException(
                'Unsupported sendmail command flags ['.$command.']. '.
                'Must be one of "-bs" or "-t" but can include additional flags.'
                ));
        }
        return $count;
    }
    protected function getBufferParams()
    {
        return $this->params;
    }
}
