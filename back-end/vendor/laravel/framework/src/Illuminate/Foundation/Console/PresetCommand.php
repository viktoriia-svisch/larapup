<?php
namespace Illuminate\Foundation\Console;
use InvalidArgumentException;
use Illuminate\Console\Command;
class PresetCommand extends Command
{
    protected $signature = 'preset
                            { type : The preset type (none, bootstrap, vue, react) }
                            { --option=* : Pass an option to the preset command }';
    protected $description = 'Swap the front-end scaffolding for the application';
    public function handle()
    {
        if (static::hasMacro($this->argument('type'))) {
            return call_user_func(static::$macros[$this->argument('type')], $this);
        }
        if (! in_array($this->argument('type'), ['none', 'bootstrap', 'vue', 'react'])) {
            throw new InvalidArgumentException('Invalid preset.');
        }
        return $this->{$this->argument('type')}();
    }
    protected function none()
    {
        Presets\None::install();
        $this->info('Frontend scaffolding removed successfully.');
    }
    protected function bootstrap()
    {
        Presets\Bootstrap::install();
        $this->info('Bootstrap scaffolding installed successfully.');
        $this->comment('Please run "npm install && npm run dev" to compile your fresh scaffolding.');
    }
    protected function vue()
    {
        Presets\Vue::install();
        $this->info('Vue scaffolding installed successfully.');
        $this->comment('Please run "npm install && npm run dev" to compile your fresh scaffolding.');
    }
    protected function react()
    {
        Presets\React::install();
        $this->info('React scaffolding installed successfully.');
        $this->comment('Please run "npm install && npm run dev" to compile your fresh scaffolding.');
    }
}
