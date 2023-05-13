<?php
namespace Illuminate\Mail;
use Aws\Ses\SesClient;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Illuminate\Log\LogManager;
use Illuminate\Support\Manager;
use GuzzleHttp\Client as HttpClient;
use Swift_SmtpTransport as SmtpTransport;
use Illuminate\Mail\Transport\LogTransport;
use Illuminate\Mail\Transport\SesTransport;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\MailgunTransport;
use Illuminate\Mail\Transport\MandrillTransport;
use Illuminate\Mail\Transport\SparkPostTransport;
use Swift_SendmailTransport as SendmailTransport;
class TransportManager extends Manager
{
    protected function createSmtpDriver()
    {
        $config = $this->app->make('config')->get('mail');
        $transport = new SmtpTransport($config['host'], $config['port']);
        if (isset($config['encryption'])) {
            $transport->setEncryption($config['encryption']);
        }
        if (isset($config['username'])) {
            $transport->setUsername($config['username']);
            $transport->setPassword($config['password']);
        }
        if (isset($config['stream'])) {
            $transport->setStreamOptions($config['stream']);
        }
        return $transport;
    }
    protected function createSendmailDriver()
    {
        return new SendmailTransport($this->app['config']['mail']['sendmail']);
    }
    protected function createSesDriver()
    {
        $config = array_merge($this->app['config']->get('services.ses', []), [
            'version' => 'latest', 'service' => 'email',
        ]);
        return new SesTransport(
            new SesClient($this->addSesCredentials($config)),
            $config['options'] ?? []
        );
    }
    protected function addSesCredentials(array $config)
    {
        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret', 'token']);
        }
        return $config;
    }
    protected function createMailDriver()
    {
        return new SendmailTransport;
    }
    protected function createMailgunDriver()
    {
        $config = $this->app['config']->get('services.mailgun', []);
        return new MailgunTransport(
            $this->guzzle($config),
            $config['secret'],
            $config['domain'],
            $config['endpoint'] ?? null
        );
    }
    protected function createMandrillDriver()
    {
        $config = $this->app['config']->get('services.mandrill', []);
        return new MandrillTransport(
            $this->guzzle($config), $config['secret']
        );
    }
    protected function createSparkPostDriver()
    {
        $config = $this->app['config']->get('services.sparkpost', []);
        return new SparkPostTransport(
            $this->guzzle($config), $config['secret'], $config['options'] ?? []
        );
    }
    protected function createLogDriver()
    {
        $logger = $this->app->make(LoggerInterface::class);
        if ($logger instanceof LogManager) {
            $logger = $logger->channel($this->app['config']['mail.log_channel']);
        }
        return new LogTransport($logger);
    }
    protected function createArrayDriver()
    {
        return new ArrayTransport;
    }
    protected function guzzle($config)
    {
        return new HttpClient(Arr::add(
            $config['guzzle'] ?? [], 'connect_timeout', 60
        ));
    }
    public function getDefaultDriver()
    {
        return $this->app['config']['mail.driver'];
    }
    public function setDefaultDriver($name)
    {
        $this->app['config']['mail.driver'] = $name;
    }
}
