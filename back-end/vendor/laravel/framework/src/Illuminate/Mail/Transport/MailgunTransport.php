<?php
namespace Illuminate\Mail\Transport;
use Swift_Mime_SimpleMessage;
use GuzzleHttp\ClientInterface;
class MailgunTransport extends Transport
{
    protected $client;
    protected $key;
    protected $domain;
    protected $endpoint;
    public function __construct(ClientInterface $client, $key, $domain, $endpoint = null)
    {
        $this->key = $key;
        $this->client = $client;
        $this->endpoint = $endpoint ?? 'api.mailgun.net';
        $this->setDomain($domain);
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $to = $this->getTo($message);
        $message->setBcc([]);
        $this->client->request(
            'POST',
            "https:
            $this->payload($message, $to)
        );
        $this->sendPerformed($message);
        return $this->numberOfRecipients($message);
    }
    protected function payload(Swift_Mime_SimpleMessage $message, $to)
    {
        return [
            'auth' => [
                'api',
                $this->key,
            ],
            'multipart' => [
                [
                    'name' => 'to',
                    'contents' => $to,
                ],
                [
                    'name' => 'message',
                    'contents' => $message->toString(),
                    'filename' => 'message.mime',
                ],
            ],
        ];
    }
    protected function getTo(Swift_Mime_SimpleMessage $message)
    {
        return collect($this->allContacts($message))->map(function ($display, $address) {
            return $display ? $display." <{$address}>" : $address;
        })->values()->implode(',');
    }
    protected function allContacts(Swift_Mime_SimpleMessage $message)
    {
        return array_merge(
            (array) $message->getTo(), (array) $message->getCc(), (array) $message->getBcc()
        );
    }
    public function getKey()
    {
        return $this->key;
    }
    public function setKey($key)
    {
        return $this->key = $key;
    }
    public function getDomain()
    {
        return $this->domain;
    }
    public function setDomain($domain)
    {
        return $this->domain = $domain;
    }
}
