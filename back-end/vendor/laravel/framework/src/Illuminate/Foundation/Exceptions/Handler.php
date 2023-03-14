<?php
namespace Illuminate\Foundation\Exceptions;
use Exception;
use Throwable;
use Whoops\Run as Whoops;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\Debug\Exception\FlattenException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
class Handler implements ExceptionHandlerContract
{
    protected $container;
    protected $dontReport = [];
    protected $internalDontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }
        if (method_exists($e, 'report')) {
            return $e->report();
        }
        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }
        $logger->error(
            $e->getMessage(),
            array_merge($this->context(), ['exception' => $e]
        ));
    }
    public function shouldReport(Exception $e)
    {
        return ! $this->shouldntReport($e);
    }
    protected function shouldntReport(Exception $e)
    {
        $dontReport = array_merge($this->dontReport, $this->internalDontReport);
        return ! is_null(Arr::first($dontReport, function ($type) use ($e) {
            return $e instanceof $type;
        }));
    }
    protected function context()
    {
        try {
            return array_filter([
                'userId' => Auth::id(),
                'email' => Auth::user() ? Auth::user()->email : null,
            ]);
        } catch (Throwable $e) {
            return [];
        }
    }
    public function render($request, Exception $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }
        $e = $this->prepareException($e);
        if ($e instanceof HttpResponseException) {
            return $e->getResponse();
        } elseif ($e instanceof AuthenticationException) {
            return $this->unauthenticated($request, $e);
        } elseif ($e instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($e, $request);
        }
        return $request->expectsJson()
                        ? $this->prepareJsonResponse($request, $e)
                        : $this->prepareResponse($request, $e);
    }
    protected function prepareException(Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new AccessDeniedHttpException($e->getMessage(), $e);
        } elseif ($e instanceof TokenMismatchException) {
            $e = new HttpException(419, $e->getMessage(), $e);
        }
        return $e;
    }
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
                    ? response()->json(['message' => $exception->getMessage()], 401)
                    : redirect()->guest($exception->redirectTo() ?? route('login'));
    }
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }
        return $request->expectsJson()
                    ? $this->invalidJson($request, $e)
                    : $this->invalid($request, $e);
    }
    protected function invalid($request, ValidationException $exception)
    {
        return redirect($exception->redirectTo ?? url()->previous())
                    ->withInput(array_except($request->input(), $this->dontFlash))
                    ->withErrors($exception->errors(), $exception->errorBag);
    }
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'message' => $exception->getMessage(),
            'errors' => $exception->errors(),
        ], $exception->status);
    }
    protected function prepareResponse($request, Exception $e)
    {
        if (! $this->isHttpException($e) && config('app.debug')) {
            return $this->toIlluminateResponse($this->convertExceptionToResponse($e), $e);
        }
        if (! $this->isHttpException($e)) {
            $e = new HttpException(500, $e->getMessage());
        }
        return $this->toIlluminateResponse(
            $this->renderHttpException($e), $e
        );
    }
    protected function convertExceptionToResponse(Exception $e)
    {
        return SymfonyResponse::create(
            $this->renderExceptionContent($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : []
        );
    }
    protected function renderExceptionContent(Exception $e)
    {
        try {
            return config('app.debug') && class_exists(Whoops::class)
                        ? $this->renderExceptionWithWhoops($e)
                        : $this->renderExceptionWithSymfony($e, config('app.debug'));
        } catch (Exception $e) {
            return $this->renderExceptionWithSymfony($e, config('app.debug'));
        }
    }
    protected function renderExceptionWithWhoops(Exception $e)
    {
        return tap(new Whoops, function ($whoops) {
            $whoops->pushHandler($this->whoopsHandler());
            $whoops->writeToOutput(false);
            $whoops->allowQuit(false);
        })->handleException($e);
    }
    protected function whoopsHandler()
    {
        return (new WhoopsHandler)->forDebug();
    }
    protected function renderExceptionWithSymfony(Exception $e, $debug)
    {
        return (new SymfonyExceptionHandler($debug))->getHtml(
            FlattenException::create($e)
        );
    }
    protected function renderHttpException(HttpException $e)
    {
        $this->registerErrorViewPaths();
        if (view()->exists($view = "errors::{$e->getStatusCode()}")) {
            return response()->view($view, [
                'errors' => new ViewErrorBag,
                'exception' => $e,
            ], $e->getStatusCode(), $e->getHeaders());
        }
        return $this->convertExceptionToResponse($e);
    }
    protected function registerErrorViewPaths()
    {
        $paths = collect(config('view.paths'));
        View::replaceNamespace('errors', $paths->map(function ($path) {
            return "{$path}/errors";
        })->push(__DIR__.'/views')->all());
    }
    protected function toIlluminateResponse($response, Exception $e)
    {
        if ($response instanceof SymfonyRedirectResponse) {
            $response = new RedirectResponse(
                $response->getTargetUrl(), $response->getStatusCode(), $response->headers->all()
            );
        } else {
            $response = new Response(
                $response->getContent(), $response->getStatusCode(), $response->headers->all()
            );
        }
        return $response->withException($e);
    }
    protected function prepareJsonResponse($request, Exception $e)
    {
        return new JsonResponse(
            $this->convertExceptionToArray($e),
            $this->isHttpException($e) ? $e->getStatusCode() : 500,
            $this->isHttpException($e) ? $e->getHeaders() : [],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );
    }
    protected function convertExceptionToArray(Exception $e)
    {
        return config('app.debug') ? [
            'message' => $e->getMessage(),
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => collect($e->getTrace())->map(function ($trace) {
                return Arr::except($trace, ['args']);
            })->all(),
        ] : [
            'message' => $this->isHttpException($e) ? $e->getMessage() : 'Server Error',
        ];
    }
    public function renderForConsole($output, Exception $e)
    {
        (new ConsoleApplication)->renderException($e, $output);
    }
    protected function isHttpException(Exception $e)
    {
        return $e instanceof HttpExceptionInterface;
    }
}
