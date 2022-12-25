<?php
namespace Illuminate\Foundation\Testing\Concerns;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
trait InteractsWithExceptionHandling
{
    protected $originalExceptionHandler;
    protected function withExceptionHandling()
    {
        if ($this->originalExceptionHandler) {
            $this->app->instance(ExceptionHandler::class, $this->originalExceptionHandler);
        }
        return $this;
    }
    protected function handleExceptions(array $exceptions)
    {
        return $this->withoutExceptionHandling($exceptions);
    }
    protected function handleValidationExceptions()
    {
        return $this->handleExceptions([ValidationException::class]);
    }
    protected function withoutExceptionHandling(array $except = [])
    {
        if ($this->originalExceptionHandler == null) {
            $this->originalExceptionHandler = app(ExceptionHandler::class);
        }
        $this->app->instance(ExceptionHandler::class, new class($this->originalExceptionHandler, $except) implements ExceptionHandler {
            protected $except;
            protected $originalHandler;
            public function __construct($originalHandler, $except = [])
            {
                $this->except = $except;
                $this->originalHandler = $originalHandler;
            }
            public function report(Exception $e)
            {
            }
            public function render($request, Exception $e)
            {
                if ($e instanceof NotFoundHttpException) {
                    throw new NotFoundHttpException(
                        "{$request->method()} {$request->url()}", null, $e->getCode()
                    );
                }
                foreach ($this->except as $class) {
                    if ($e instanceof $class) {
                        return $this->originalHandler->render($request, $e);
                    }
                }
                throw $e;
            }
            public function renderForConsole($output, Exception $e)
            {
                (new ConsoleApplication)->renderException($e, $output);
            }
        });
        return $this;
    }
}
