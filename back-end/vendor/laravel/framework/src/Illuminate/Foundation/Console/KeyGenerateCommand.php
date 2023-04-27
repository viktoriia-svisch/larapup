<?php
namespace Illuminate\Foundation\Console;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Console\ConfirmableTrait;
class KeyGenerateCommand extends Command
{
    use ConfirmableTrait;
    protected $signature = 'key:generate
                    {--show : Display the key instead of modifying files}
                    {--force : Force the operation to run when in production}';
    protected $description = 'Set the application key';
    public function handle()
    {
        $key = $this->generateRandomKey();
        if ($this->option('show')) {
            return $this->line('<comment>'.$key.'</comment>');
        }
        if (! $this->setKeyInEnvironmentFile($key)) {
            return;
        }
        $this->laravel['config']['app.key'] = $key;
        $this->info('Application key set successfully.');
    }
    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey($this->laravel['config']['app.cipher'])
        );
    }
    protected function setKeyInEnvironmentFile($key)
    {
        $currentKey = $this->laravel['config']['app.key'];
        if (strlen($currentKey) !== 0 && (! $this->confirmToProceed())) {
            return false;
        }
        $this->writeNewEnvironmentFileWith($key);
        return true;
    }
    protected function writeNewEnvironmentFileWith($key)
    {
        file_put_contents($this->laravel->environmentFilePath(), preg_replace(
            $this->keyReplacementPattern(),
            'APP_KEY='.$key,
            file_get_contents($this->laravel->environmentFilePath())
        ));
    }
    protected function keyReplacementPattern()
    {
        $escaped = preg_quote('='.$this->laravel['config']['app.key'], '/');
        return "/^APP_KEY{$escaped}/m";
    }
}
