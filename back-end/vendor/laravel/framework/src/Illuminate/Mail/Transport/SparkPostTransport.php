<?php
namespace Illuminate\Mail\Transport;
use Swift_Mime_SimpleMessage;
use GuzzleHttp\ClientInterface;
class SparkPostTransport extends Transport
{
    protected $client;
    protected $key;
    protected $options = [];
    public function __construct(ClientInterface $client, $key, $options = [])
    {
        $this->key = $key;
        $this->client = $client;
        $this->options = $options;
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $recipients = $this->getRecipients($message);
        $message->setBcc([]);
        $response = $this->client->request('POST', $this->getEndpoint(), [
            'headers' => [
                'Authorization' => $this->key,
            ],
            'json' => array_merge([
                'recipients' => $recipients,
                'content' => [
                    'email_rfc822' => $message->toString(),
                ],
            ], $this->options),
        ]);
        $message->getHeaders()->addTextHeader(
            'X-SparkPost-Transmission-ID', $this->getTransmissionId($response)
        );
        $this->sendPerformed($message);
        return $this->numberOfRecipients($message);
    }
    protected function getRecipients(Swift_Mime_SimpleMessage $message)
    {
        $recipients = [];
        foreach ((array) $message->getTo() as $email => $name) {
            $recipients[] = ['address' => compact('name', 'email')];
        }
        foreach ((array) $message->getCc() as $email => $name) {
            $recipients[] = ['address' => compact('name', 'email')];
        }
        foreach ((array) $message->getBcc() as $email => $name) {
            $recipients[] = ['address' => compact('name', 'email')];
        }
        return $recipients;
    }
    protected function getTransmissionId($response)
    {
        return object_get(
            json_decode($response->getBody()->getContents()), 'results.id'
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
    public function getEndpoint()
    {
        return $this->getOptions()['endpoint'] ?? 'https:
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function setOptions(array $options)
    {
        return $this->options = $options;
    }
}
