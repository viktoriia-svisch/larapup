<?php
namespace Illuminate\View\Middleware;
use Closure;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Contracts\View\Factory as ViewFactory;
class ShareErrorsFromSession
{
    protected $view;
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }
    public function handle($request, Closure $next)
    {
        $this->view->share(
            'errors', $request->session()->get('errors') ?: new ViewErrorBag
        );
        return $next($request);
    }
}
