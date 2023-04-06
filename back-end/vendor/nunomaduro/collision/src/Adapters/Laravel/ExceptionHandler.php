<?php
namespace NunoMaduro\Collision\Adapters\Laravel;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use NunoMaduro\Collision\Contracts\Provider as ProviderContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleExceptionInterface;
class ExceptionHandler implements ExceptionHandlerContract
{
    protected $appExceptionHandler;
    protected $app;
    public function __construct(Application $app, ExceptionHandlerContract $appExceptionHandler)
    {
        $this->app = $app;
        $this->appExceptionHandler = $appExceptionHandler;
    }
    public function report(Exception $e)
    {
        $this->appExceptionHandler->report($e);
    }
    public function render($request, Exception $e)
    {
        return $this->appExceptionHandler->render($request, $e);
    }
    public function renderForConsole($output, Exception $e)
    {
        if ($e instanceof SymfonyConsoleExceptionInterface) {
            $this->appExceptionHandler->renderForConsole($output, $e);
        } else {
            $handler = $this->app->make(ProviderContract::class)
                ->register()
                ->getHandler()
                ->setOutput($output);
            $handler->setInspector((new Inspector($e)));
            $handler->handle();
        }
    }
    public function shouldReport(Exception $e)
    {
        return $this->appExceptionHandler->shouldReport($e);
    }
}
