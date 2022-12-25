<?php
namespace Illuminate\Mail\Transport;
use Aws\Ses\SesClient;
use Swift_Mime_SimpleMessage;
class SesTransport extends Transport
{
    protected $ses;
    protected $options = [];
    public function __construct(SesClient $ses, $options = [])
    {
        $this->ses = $ses;
        $this->options = $options;
    }
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->beforeSendPerformed($message);
        $result = $this->ses->sendRawEmail(
            array_merge(
                $this->options, [
                    'Source' => key($message->getSender() ?: $message->getFrom()),
                    'RawMessage' => [
                        'Data' => $message->toString(),
                    ],
                ]
            )
        );
        $message->getHeaders()->addTextHeader('X-SES-Message-ID', $result->get('MessageId'));
        $this->sendPerformed($message);
        return $this->numberOfRecipients($message);
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
