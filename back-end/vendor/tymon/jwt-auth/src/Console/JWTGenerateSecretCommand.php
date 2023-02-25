<?php
namespace Tymon\JWTAuth\Console;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
class JWTGenerateSecretCommand extends Command
{
    protected $signature = 'jwt:secret
        {--s|show : Display the key instead of modifying files.}
        {--f|force : Skip confirmation when overwriting an existing key.}';
    protected $description = 'Set the JWTAuth secret key used to sign the tokens';
    public function handle()
    {
        $key = Str::random(32);
        if ($this->option('show')) {
            $this->comment($key);
            return;
        }
        if (file_exists($path = $this->envPath()) === false) {
            return $this->displayKey($key);
        }
        if (Str::contains(file_get_contents($path), 'JWT_SECRET') === false) {
            file_put_contents($path, PHP_EOL."JWT_SECRET=$key", FILE_APPEND);
        } else {
            if ($this->isConfirmed() === false) {
                $this->comment('Phew... No changes were made to your secret key.');
                return;
            }
            file_put_contents($path, str_replace(
                'JWT_SECRET='.$this->laravel['config']['jwt.secret'],
                'JWT_SECRET='.$key, file_get_contents($path)
            ));
        }
        $this->displayKey($key);
    }
    protected function displayKey($key)
    {
        $this->laravel['config']['jwt.secret'] = $key;
        $this->info("jwt-auth secret [$key] set successfully.");
    }
    protected function isConfirmed()
    {
        return $this->option('force') ? true : $this->confirm(
            'This will invalidate all existing tokens. Are you sure you want to override the secret key?'
        );
    }
    protected function envPath()
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        }
        if (version_compare($this->laravel->version(), '5.4.17', '<')) {
            return $this->laravel->basePath().DIRECTORY_SEPARATOR.'.env';
        }
        return $this->laravel->basePath('.env');
    }
}
