<?php
namespace Illuminate\Foundation\Bootstrap;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use Symfony\Component\Console\Input\ArgvInput;
use Illuminate\Contracts\Foundation\Application;
class LoadEnvironmentVariables
{
    public function bootstrap(Application $app)
    {
        if ($app->configurationIsCached()) {
            return;
        }
        $this->checkForSpecificEnvironmentFile($app);
        try {
            (new Dotenv($app->environmentPath(), $app->environmentFile()))->load();
        } catch (InvalidPathException $e) {
        } catch (InvalidFileException $e) {
            echo 'The environment file is invalid: '.$e->getMessage();
            die(1);
        }
    }
    protected function checkForSpecificEnvironmentFile($app)
    {
        if ($app->runningInConsole() && ($input = new ArgvInput)->hasParameterOption('--env')) {
            if ($this->setEnvironmentFilePath(
                $app, $app->environmentFile().'.'.$input->getParameterOption('--env')
            )) {
                return;
            }
        }
        if (! env('APP_ENV')) {
            return;
        }
        $this->setEnvironmentFilePath(
            $app, $app->environmentFile().'.'.env('APP_ENV')
        );
    }
    protected function setEnvironmentFilePath($app, $file)
    {
        if (file_exists($app->environmentPath().'/'.$file)) {
            $app->loadEnvironmentFrom($file);
            return true;
        }
        return false;
    }
}
