<?php
namespace Illuminate\Mail;
use Swift_Mailer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Swift_DependencyContainer;
use Illuminate\Support\ServiceProvider;
class MailServiceProvider extends ServiceProvider
{
    protected $defer = true;
    public function register()
    {
        $this->registerSwiftMailer();
        $this->registerIlluminateMailer();
        $this->registerMarkdownRenderer();
    }
    protected function registerIlluminateMailer()
    {
        $this->app->singleton('mailer', function () {
            $config = $this->app->make('config')->get('mail');
            $mailer = new Mailer(
                $this->app['view'],
                $this->app['swift.mailer'],
                $this->app['events']
            );
            if ($this->app->bound('queue')) {
                $mailer->setQueue($this->app['queue']);
            }
            foreach (['from', 'reply_to', 'to'] as $type) {
                $this->setGlobalAddress($mailer, $config, $type);
            }
            return $mailer;
        });
    }
    protected function setGlobalAddress($mailer, array $config, $type)
    {
        $address = Arr::get($config, $type);
        if (is_array($address) && isset($address['address'])) {
            $mailer->{'always'.Str::studly($type)}($address['address'], $address['name']);
        }
    }
    public function registerSwiftMailer()
    {
        $this->registerSwiftTransport();
        $this->app->singleton('swift.mailer', function () {
            if ($domain = $this->app->make('config')->get('mail.domain')) {
                Swift_DependencyContainer::getInstance()
                                ->register('mime.idgenerator.idright')
                                ->asValue($domain);
            }
            return new Swift_Mailer($this->app['swift.transport']->driver());
        });
    }
    protected function registerSwiftTransport()
    {
        $this->app->singleton('swift.transport', function () {
            return new TransportManager($this->app);
        });
    }
    protected function registerMarkdownRenderer()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/resources/views' => $this->app->resourcePath('views/vendor/mail'),
            ], 'laravel-mail');
        }
        $this->app->singleton(Markdown::class, function () {
            $config = $this->app->make('config');
            return new Markdown($this->app->make('view'), [
                'theme' => $config->get('mail.markdown.theme', 'default'),
                'paths' => $config->get('mail.markdown.paths', []),
            ]);
        });
    }
    public function provides()
    {
        return [
            'mailer', 'swift.mailer', 'swift.transport', Markdown::class,
        ];
    }
}
