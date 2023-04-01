<?php
namespace Illuminate\Routing;
use Illuminate\Contracts\View\Factory as ViewFactory;
class ViewController extends Controller
{
    protected $view;
    public function __construct(ViewFactory $view)
    {
        $this->view = $view;
    }
    public function __invoke(...$args)
    {
        [$view, $data] = array_slice($args, -2);
        return $this->view->make($view, $data);
    }
}
