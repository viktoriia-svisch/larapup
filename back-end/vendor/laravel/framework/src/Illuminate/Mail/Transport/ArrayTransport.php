<?php
namespace Illuminate\Mail\Transport;
use Swift_Mime_SimpleMessage;
use Illuminate\Support\Collection;
class ArrayTransport extends Transport
{
    protected $messages;
    public function __construct()
    {
        $this->messages = new Collection;
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $this->messages[] = $message;
        return $this->numberOfRecipients($message);
    }
    public function messages()
    {
        return $this->messages;
    }
    public function flush()
    {
        return $this->messages = new Collection;
    }
}
