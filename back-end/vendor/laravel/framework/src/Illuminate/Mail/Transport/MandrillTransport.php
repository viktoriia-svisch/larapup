<?php
namespace Illuminate\Mail\Transport;
use Swift_Mime_SimpleMessage;
use GuzzleHttp\ClientInterface;
class MandrillTransport extends Transport
{
    protected $client;
    protected $key;
    public function __construct(ClientInterface $client, $key)
    {
        $this->key = $key;
        $this->client = $client;
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $this->client->request('POST', 'https:
            'form_params' => [
                'key' => $this->key,
                'to' => $this->getTo($message),
                'raw_message' => $message->toString(),
                'async' => true,
            ],
        ]);
        $this->sendPerformed($message);
        return $this->numberOfRecipients($message);
    }
    protected function getTo(Swift_Mime_SimpleMessage $message)
    {
        $to = [];
        if ($message->getTo()) {
            $to = array_merge($to, array_keys($message->getTo()));
        }
        if ($message->getCc()) {
            $to = array_merge($to, array_keys($message->getCc()));
        }
        if ($message->getBcc()) {
            $to = array_merge($to, array_keys($message->getBcc()));
        }
        return $to;
    }
    public function getKey()
    {
        return $this->key;
    }
    public function setKey($key)
    {
        return $this->key = $key;
    }
}
