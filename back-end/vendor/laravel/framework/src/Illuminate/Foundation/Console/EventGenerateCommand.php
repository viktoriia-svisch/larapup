<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
class EventGenerateCommand extends Command
{
    protected $name = 'event:generate';
    protected $description = 'Generate the missing events and listeners based on registration';
    public function handle()
    {
        $providers = $this->laravel->getProviders(EventServiceProvider::class);
        foreach ($providers as $provider) {
            foreach ($provider->listens() as $event => $listeners) {
                $this->makeEventAndListeners($event, $listeners);
            }
        }
        $this->info('Events and listeners generated successfully!');
    }
    protected function makeEventAndListeners($event, $listeners)
    {
        if (! Str::contains($event, '\\')) {
            return;
        }
        $this->callSilent('make:event', ['name' => $event]);
        $this->makeListeners($event, $listeners);
    }
    protected function makeListeners($event, $listeners)
    {
        foreach ($listeners as $listener) {
            $listener = preg_replace('/@.+$/', '', $listener);
            $this->callSilent('make:listener', array_filter(
                ['name' => $listener, '--event' => $event]
            ));
        }
    }
}
