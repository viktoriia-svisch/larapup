<?php
namespace Illuminate\Routing;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Contracts\Routing\ResponseFactory as FactoryContract;
class ResponseFactory implements FactoryContract
{
    use Macroable;
    protected $view;
    protected $redirector;
    public function __construct(ViewFactory $view, Redirector $redirector)
    {
        $this->view = $view;
        $this->redirector = $redirector;
    }
    public function make($content = '', $status = 200, array $headers = [])
    {
        return new Response($content, $status, $headers);
    }
    public function noContent($status = 204, array $headers = [])
    {
        return $this->make('', $status, $headers);
    }
    public function view($view, $data = [], $status = 200, array $headers = [])
    {
        return $this->make($this->view->make($view, $data), $status, $headers);
    }
    public function json($data = [], $status = 200, array $headers = [], $options = 0)
    {
        return new JsonResponse($data, $status, $headers, $options);
    }
    public function jsonp($callback, $data = [], $status = 200, array $headers = [], $options = 0)
    {
        return $this->json($data, $status, $headers, $options)->setCallback($callback);
    }
    public function stream($callback, $status = 200, array $headers = [])
    {
        return new StreamedResponse($callback, $status, $headers);
    }
    public function streamDownload($callback, $name = null, array $headers = [], $disposition = 'attachment')
    {
        $response = new StreamedResponse($callback, 200, $headers);
        if (! is_null($name)) {
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                $disposition,
                $name,
                $this->fallbackName($name)
            ));
        }
        return $response;
    }
    public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
    {
        $response = new BinaryFileResponse($file, 200, $headers, true, $disposition);
        if (! is_null($name)) {
            return $response->setContentDisposition($disposition, $name, $this->fallbackName($name));
        }
        return $response;
    }
    protected function fallbackName($name)
    {
        return str_replace('%', '', Str::ascii($name));
    }
    public function file($file, array $headers = [])
    {
        return new BinaryFileResponse($file, 200, $headers);
    }
    public function redirectTo($path, $status = 302, $headers = [], $secure = null)
    {
        return $this->redirector->to($path, $status, $headers, $secure);
    }
    public function redirectToRoute($route, $parameters = [], $status = 302, $headers = [])
    {
        return $this->redirector->route($route, $parameters, $status, $headers);
    }
    public function redirectToAction($action, $parameters = [], $status = 302, $headers = [])
    {
        return $this->redirector->action($action, $parameters, $status, $headers);
    }
    public function redirectGuest($path, $status = 302, $headers = [], $secure = null)
    {
        return $this->redirector->guest($path, $status, $headers, $secure);
    }
    public function redirectToIntended($default = '/', $status = 302, $headers = [], $secure = null)
    {
        return $this->redirector->intended($default, $status, $headers, $secure);
    }
}
